<?php

namespace App\Http\Controllers;

use App\Models\Region;
use App\Models\RegionItem;
use App\Models\Selectdocslogs;
use App\Models\StsAttachment;
use App\Models\Uploadlog;
use App\Models\User;
use App\Services\RegionSheetImportService;
use App\Support\MasterDataRegionCatalog;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Http;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Str;
use Illuminate\View\View as ViewContract;
use Illuminate\View\View;
use App\Models\SocialTechnologyTitle;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
use PhpOffice\PhpSpreadsheet\IOFactory;

class MasterDataController extends Controller
{
    public function index(Request $request): View
    {
        $actorName = $this->resolveActorName();
        $this->ensureDefaultRegions($actorName);
        $this->backfillAuditNames($actorName);

        $regions = Region::query()
            ->withCount('items')
            ->get();

        $regions = $regions
            ->sortBy(fn (Region $region) => MasterDataRegionCatalog::orderOf($region->name))
            ->values();

        $selectedRegionName = MasterDataRegionCatalog::normalize($request->query('region_filter'));
        if (!$selectedRegionName) {
            $selectedRegionName = $regions->first()?->name;
        }

        $allRegionItems = RegionItem::query()
            ->with('region:id,name')
            ->latest('updated_at')
            ->get();

        $updatePanel = $this->buildUpdatesPanelData($request, $regions, $allRegionItems);

        $overview = $this->buildOverview($regions, $allRegionItems);

        $currentGoogleSheetFile = $this->resolveCurrentGoogleSheetFile();
        $currentGoogleSheetUrl = null;
        if ($currentGoogleSheetFile && preg_match('/^gsheet_([a-zA-Z0-9-_]+)_/', $currentGoogleSheetFile, $matches)) {
            $currentGoogleSheetUrl = 'https://docs.google.com/spreadsheets/d/' . $matches[1] . '/edit';
        }

        return view('dashboard.maincomponents.masterdata', [
            'regions' => $regions,
            'allRegionItems' => $allRegionItems,
            'overview' => $overview,
            'currentGoogleSheetFile' => $currentGoogleSheetFile,
            'currentGoogleSheetUrl' => $currentGoogleSheetUrl,
            'activeTab' => $request->query('tab', 'overview') === 'updates' ? 'updates' : 'overview',
            ...$updatePanel,
        ]);
    }

    public function exportRegionItems()
    {
        $items = RegionItem::query()
            ->with('region:id,name')
            ->orderBy('region_id')
            ->orderBy('title')
            ->get();

        $spreadsheet = new Spreadsheet();
        $sheet = $spreadsheet->getActiveSheet();

        $headers = [
            'Region Number',
            'ST Title',
            'Status',
            'Province',
            'Municipality',
            'Adopted/Replicated',
            'With Expression of Interest',
            'With MOA',
            'With Resolution',
            'Included AIP',
            'Year of MOA',
            'Year of Resolution',
        ];

        $sheet->fromArray($headers, null, 'A1');

        $row = 2;
        foreach ($items as $item) {
            $status = Str::lower((string) $item->status);
            if ($status === 'ongoing') {
                $statusLabel = 'Ongoing';
            } elseif ($status === 'dissolved') {
                $statusLabel = 'Dissolved';
            } else {
                $statusLabel = 'Unspecified';
            }

            $adoption = $item->with_adopted ? 'Adopted' : ($item->with_replicated ? 'Replicated' : 'None');

            $sheet->setCellValue('A' . $row, $item->region?->name ?? '');
            $sheet->setCellValue('B' . $row, $item->title ?? '');
            $sheet->setCellValue('C' . $row, $statusLabel);
            $sheet->setCellValue('D' . $row, $item->province ?? '');
            $sheet->setCellValue('E' . $row, $item->municipality ?? '');
            $sheet->setCellValue('F' . $row, $adoption);
            $sheet->setCellValue('G' . $row, $item->with_expr ? 'TRUE' : 'FALSE');
            $sheet->setCellValue('H' . $row, $item->with_moa ? 'TRUE' : 'FALSE');
            $sheet->setCellValue('I' . $row, $item->with_res ? 'TRUE' : 'FALSE');
            $sheet->setCellValue('J' . $row, $item->included_aip ? 'TRUE' : 'FALSE');
            $sheet->setCellValue('K' . $row, $item->year_of_moa ?? '');
            $sheet->setCellValue('L' . $row, $item->year_of_resolution ?? '');

            $row++;
        }

        $writer = new Xlsx($spreadsheet);
        $fileName = 'region_items_export_' . date('Ymd_His') . '.xlsx';

        $callback = function () use ($writer) {
            $writer->save('php://output');
        };

        return response()->streamDownload($callback, $fileName, [
            'Content-Type' => 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
        ]);
    }

    public function importRegionItemsExcel(Request $request)
    {
        $request->validate([
            'region_items_excel' => ['required', 'file', 'mimes:xlsx,xls'],
        ]);

        $actorName = $this->resolveActorName();

        $file = $request->file('region_items_excel');
        $path = $file->getRealPath();

        $reader = IOFactory::createReaderForFile($path);
        $reader->setReadDataOnly(true);
        $spreadsheet = $reader->load($path);
        $sheet = $spreadsheet->getActiveSheet();
        $rows = $sheet->toArray(null, true, true, true);

        if (empty($rows) || count($rows) < 2) {
            return redirect()->route('masterdata.index', ['tab' => 'overview'])->with('error', 'Uploaded file contains no data.');
        }

            // Detect header row (header may not always be in row 1). Search first 5 rows for a row containing 'title'.
            $headerIndex = 1;
            $headerRow = array_map('trim', (array) $rows[$headerIndex]);

            // Map header names to column letters
            $map = [];
            foreach ($headerRow as $col => $val) {
                $norm = strtolower($val);
                if (str_contains($norm, 'region')) {
                    $map['region'] = strtoupper($col);
                } elseif (str_contains($norm, 'title')) {
                    $map['title'] = strtoupper($col);
                } elseif (str_contains($norm, 'status')) {
                    $map['status'] = strtoupper($col);
                } elseif (str_contains($norm, 'province')) {
                    $map['province'] = strtoupper($col);
                } elseif (str_contains($norm, 'municipality')) {
                    $map['municipality'] = strtoupper($col);
                } elseif (str_contains($norm, 'adopt')) {
                    $map['adoption'] = strtoupper($col);
                } elseif (str_contains($norm, 'expression') || str_contains($norm, 'expr')) {
                    $map['with_expr'] = strtoupper($col);
                } elseif (str_contains($norm, 'moa') && str_contains($norm, 'year')) {
                    $map['year_of_moa'] = strtoupper($col);
                } elseif (str_contains($norm, 'moa')) {
                    $map['with_moa'] = strtoupper($col);
                } elseif (str_contains($norm, 'resolution') || str_contains($norm, 'res')) {
                    if (str_contains($norm, 'year')) {
                        $map['year_of_resolution'] = strtoupper($col);
                    } else {
                        $map['with_res'] = strtoupper($col);
                    }
                } elseif (str_contains($norm, 'aip')) {
                    $map['included_aip'] = strtoupper($col);
                }
            }

        // If title column wasn't found, try searching the first 5 rows for the header row
        if (!isset($map['title'])) {
            $maxCheck = min(5, count($rows));
            for ($i = 1; $i <= $maxCheck; $i++) {
                $candidate = array_map('trim', (array) $rows[$i]);
                $foundTitle = false;
                foreach ($candidate as $c) {
                    if (is_string($c) && str_contains(strtolower($c), 'title')) {
                        $foundTitle = true;
                        break;
                    }
                }
                if ($foundTitle) {
                    $headerIndex = $i;
                    $headerRow = $candidate;
                    $map = [];
                    foreach ($headerRow as $col => $val) {
                        $norm = strtolower($val);
                        if (str_contains($norm, 'region')) {
                            $map['region'] = strtoupper($col);
                        } elseif (str_contains($norm, 'title')) {
                            $map['title'] = strtoupper($col);
                        } elseif (str_contains($norm, 'status')) {
                            $map['status'] = strtoupper($col);
                        } elseif (str_contains($norm, 'province')) {
                            $map['province'] = strtoupper($col);
                        } elseif (str_contains($norm, 'municipality')) {
                            $map['municipality'] = strtoupper($col);
                        } elseif (str_contains($norm, 'adopt')) {
                            $map['adoption'] = strtoupper($col);
                        } elseif (str_contains($norm, 'expression') || str_contains($norm, 'expr')) {
                            $map['with_expr'] = strtoupper($col);
                        } elseif (str_contains($norm, 'moa') && str_contains($norm, 'year')) {
                            $map['year_of_moa'] = strtoupper($col);
                        } elseif (str_contains($norm, 'moa')) {
                            $map['with_moa'] = strtoupper($col);
                        } elseif (str_contains($norm, 'resolution') || str_contains($norm, 'res')) {
                            if (str_contains($norm, 'year')) {
                                $map['year_of_resolution'] = strtoupper($col);
                            } else {
                                $map['with_res'] = strtoupper($col);
                            }
                        } elseif (str_contains($norm, 'aip')) {
                            $map['included_aip'] = strtoupper($col);
                        }
                    }
                    break;
                }
            }
        }

        $created = 0;
        $updated = 0;
        $skipped = 0;
        $warnings = [];

        // Prepare a small debug sample of first 10 rows (after detected header)
        $sampleRows = array_slice($rows, $headerIndex, 10);
        $debugSample = ['header_index' => $headerIndex, 'map' => $map, 'rows' => []];
        foreach ($sampleRows as $i => $r) {
            $rowNum = $headerIndex + $i + 1;
            $debugSample['rows'][] = [
                'sheet_row' => $rowNum,
                'region_raw' => isset($map['region']) ? ($r[$map['region']] ?? '') : null,
                'title_raw' => isset($map['title']) ? ($r[$map['title']] ?? '') : null,
            ];
        }
        session()->flash('masterdata_import_debug', $debugSample);

        foreach (array_slice($rows, $headerIndex) as $idx => $row) {
            $sheetRowNumber = $headerIndex + $idx + 1; // headerIndex + 1 is first data row
            // Using associative access by column letter
            $regionName = isset($map['region']) ? trim((string) ($row[$map['region']] ?? '')) : '';
            $title = isset($map['title']) ? trim((string) ($row[$map['title']] ?? '')) : '';

            if ($title === '') {
                $skipped++;
                $warnings[] = "Row {$sheetRowNumber}: title empty, skipped.";
                continue;
            }

            $province = isset($map['province']) ? trim((string) ($row[$map['province']] ?? '')) : null;
            $municipality = isset($map['municipality']) ? trim((string) ($row[$map['municipality']] ?? '')) : null;

            // Robust region lookup: try exact, case-insensitive, strip common prefixes like 'FO ', then contains match
            $region = null;
            if ($regionName !== '') {
                $candidate = trim((string) $regionName);
                $candidateNormalized = preg_replace('/^fo\s+[-_:]?\s*/i', '', $candidate);
                $candidateNormalized = trim($candidateNormalized);

                $region = Region::query()->where('name', $candidate)->first();
                if (!$region) {
                    $region = Region::query()->whereRaw('LOWER(name) = ?', [strtolower($candidate)])->first();
                }
                if (!$region && $candidateNormalized !== $candidate) {
                    $region = Region::query()->whereRaw('LOWER(name) = ?', [strtolower($candidateNormalized)])->first();
                }
                if (!$region) {
                    $allRegions = Region::query()->get();
                    $region = $allRegions->firstWhere(fn ($r) => str_contains(strtolower($candidate), strtolower($r->name)) || str_contains(strtolower($r->name), strtolower($candidate)));
                }
            }

            if (!$region) {
                $skipped++;
                $warnings[] = "Row {$sheetRowNumber}: region not found ('{$regionName}')";
                continue;
            }

            // Ensure social technology title exists (case-insensitive)
            $titleExists = SocialTechnologyTitle::query()->whereRaw('LOWER(social_technology) = ?', [strtolower($title)])->exists();
            if (!$titleExists) {
                $skipped++;
                $warnings[] = "Row {$sheetRowNumber}: title not found ('{$title}')";
                continue;
            }

            $statusRaw = isset($map['status']) ? strtolower(trim((string) ($row[$map['status']] ?? ''))) : '';
            $status = null;
            if ($statusRaw === 'ongoing') {
                $status = 'ongoing';
            } elseif ($statusRaw === 'dissolved') {
                $status = 'dissolved';
            }

            $adoptionRaw = isset($map['adoption']) ? strtolower(trim((string) ($row[$map['adoption']] ?? ''))) : '';
            $with_adopted = false;
            $with_replicated = false;
            if (str_contains($adoptionRaw, 'adopt')) {
                $with_adopted = true;
            } elseif (str_contains($adoptionRaw, 'replic')) {
                $with_replicated = true;
            }

            $toBool = function ($v) {
                $v = strtolower(trim((string) ($v ?? '')));
                if ($v === 'true' || $v === '1' || $v === 'yes') {
                    return true;
                }
                return false;
            };

            $with_expr = isset($map['with_expr']) ? $toBool($row[$map['with_expr']]) : false;
            $with_moa = isset($map['with_moa']) ? $toBool($row[$map['with_moa']]) : false;
            $with_res = isset($map['with_res']) ? $toBool($row[$map['with_res']]) : false;
            $included_aip = isset($map['included_aip']) ? $toBool($row[$map['included_aip']]) : false;

            $identity = [
                'region_id' => $region->id,
                'title' => $title,
                'province' => $province ?: null,
                'municipality' => $municipality ?: null,
            ];

            $payload = [
                'status' => $status,
                'with_expr' => $with_expr,
                'with_moa' => $with_moa,
                'with_res' => $with_res,
                'included_aip' => $included_aip,
                'with_adopted' => $with_adopted,
                'with_replicated' => $with_replicated,
                'updatedby' => $actorName,
            ];

            $existing = RegionItem::query()->where($identity)->first();
            if ($existing) {
                $existing->update($payload);
                $updated++;
            } else {
                $payload = array_merge($identity, $payload, ['createdby' => $actorName]);
                RegionItem::query()->create($payload);
                $created++;
            }
        }

        $message = "Imported file: created={$created}, updated={$updated}, skipped={$skipped}.";
        if ($warnings) {
            session()->flash('masterdata_import_warnings', $warnings);
        }

        return redirect()->route('masterdata.index', ['tab' => 'overview'])->with('status', $message);
    }

    /**
     * Force-import region items from an uploaded Excel file.
     * This will create or update RegionItem rows directly, mapping the region name
     * from the sheet to the `regions.id`. Unlike the other importer, this will
     * not require the title to exist in `social_technology_titles`.
     */
    public function importRegionItemsExcelForce(Request $request)
    {
        $request->validate([
            'region_items_excel' => ['required', 'file', 'mimes:xlsx,xls'],
        ]);

        $actorName = $this->resolveActorName();

        $file = $request->file('region_items_excel');
        $path = $file->getRealPath();

        $reader = IOFactory::createReaderForFile($path);
        $reader->setReadDataOnly(true);
        $spreadsheet = $reader->load($path);
        $sheet = $spreadsheet->getActiveSheet();
        $rows = $sheet->toArray(null, true, true, true);

        if (empty($rows) || count($rows) < 2) {
            return redirect()->route('masterdata.index', ['tab' => 'overview'])->with('error', 'Uploaded file contains no data.');
        }

        // Detect header row (header may not always be in row 1). Search first 5 rows for a row containing 'title'.
        $headerIndex = 1;
        $headerRow = array_map('trim', (array) $rows[$headerIndex]);

        // Map header names to column letters (including years)
        $map = [];
        foreach ($headerRow as $col => $val) {
            $norm = strtolower($val);
            if (str_contains($norm, 'region')) {
                $map['region'] = strtoupper($col);
            } elseif (str_contains($norm, 'title')) {
                $map['title'] = strtoupper($col);
            } elseif (str_contains($norm, 'status')) {
                $map['status'] = strtoupper($col);
            } elseif (str_contains($norm, 'province')) {
                $map['province'] = strtoupper($col);
            } elseif (str_contains($norm, 'municipality')) {
                $map['municipality'] = strtoupper($col);
            } elseif (str_contains($norm, 'adopt')) {
                $map['adoption'] = strtoupper($col);
            } elseif (str_contains($norm, 'expression') || str_contains($norm, 'expr')) {
                $map['with_expr'] = strtoupper($col);
            } elseif (str_contains($norm, 'moa') && str_contains($norm, 'year')) {
                $map['year_of_moa'] = strtoupper($col);
            } elseif (str_contains($norm, 'moa')) {
                $map['with_moa'] = strtoupper($col);
            } elseif (str_contains($norm, 'resolution') || str_contains($norm, 'res')) {
                if (str_contains($norm, 'year')) {
                    $map['year_of_resolution'] = strtoupper($col);
                } else {
                    $map['with_res'] = strtoupper($col);
                }
            } elseif (str_contains($norm, 'aip')) {
                $map['included_aip'] = strtoupper($col);
            }
        }

        // If title column wasn't found, try searching the first 5 rows for the header row
        if (!isset($map['title'])) {
            $maxCheck = min(5, count($rows));
            for ($i = 1; $i <= $maxCheck; $i++) {
                $candidate = array_map('trim', (array) $rows[$i]);
                $foundTitle = false;
                foreach ($candidate as $c) {
                    if (is_string($c) && str_contains(strtolower($c), 'title')) {
                        $foundTitle = true;
                        break;
                    }
                }
                if ($foundTitle) {
                    $headerIndex = $i;
                    $headerRow = $candidate;
                    $map = [];
                    foreach ($headerRow as $col => $val) {
                        $norm = strtolower($val);
                        if (str_contains($norm, 'region')) {
                            $map['region'] = strtoupper($col);
                        } elseif (str_contains($norm, 'title')) {
                            $map['title'] = strtoupper($col);
                        } elseif (str_contains($norm, 'status')) {
                            $map['status'] = strtoupper($col);
                        } elseif (str_contains($norm, 'province')) {
                            $map['province'] = strtoupper($col);
                        } elseif (str_contains($norm, 'municipality')) {
                            $map['municipality'] = strtoupper($col);
                        } elseif (str_contains($norm, 'adopt')) {
                            $map['adoption'] = strtoupper($col);
                        } elseif (str_contains($norm, 'expression') || str_contains($norm, 'expr')) {
                            $map['with_expr'] = strtoupper($col);
                        } elseif (str_contains($norm, 'moa') && str_contains($norm, 'year')) {
                            $map['year_of_moa'] = strtoupper($col);
                        } elseif (str_contains($norm, 'moa')) {
                            $map['with_moa'] = strtoupper($col);
                        } elseif (str_contains($norm, 'resolution') || str_contains($norm, 'res')) {
                            if (str_contains($norm, 'year')) {
                                $map['year_of_resolution'] = strtoupper($col);
                            } else {
                                $map['with_res'] = strtoupper($col);
                            }
                        } elseif (str_contains($norm, 'aip')) {
                            $map['included_aip'] = strtoupper($col);
                        }
                    }
                    break;
                }
            }
        }

        $created = 0;
        $updated = 0;
        $skipped = 0;
        $warnings = [];

        // iterate starting from detected header row
        $dataRows = array_slice($rows, $headerIndex);
        foreach ($dataRows as $idx => $row) {
            $sheetRowNumber = $idx + $headerIndex + 1; // account for header at $headerIndex

            $regionName = isset($map['region']) ? trim((string) ($row[$map['region']] ?? '')) : '';
            $title = isset($map['title']) ? trim((string) ($row[$map['title']] ?? '')) : '';

            if ($title === '') {
                $skipped++;
                $warnings[] = "Row {$sheetRowNumber}: title empty, skipped.";
                continue;
            }

            $province = isset($map['province']) ? trim((string) ($row[$map['province']] ?? '')) : null;
            $municipality = isset($map['municipality']) ? trim((string) ($row[$map['municipality']] ?? '')) : null;

            $region = null;
            if ($regionName !== '') {
                $region = Region::query()->where('name', $regionName)->first();
            }

            if (!$region) {
                $skipped++;
                $warnings[] = "Row {$sheetRowNumber}: region not found ('{$regionName}'), skipped.";
                continue;
            }

            $statusRaw = isset($map['status']) ? strtolower(trim((string) ($row[$map['status']] ?? ''))) : '';
            $status = null;
            if ($statusRaw === 'ongoing') {
                $status = 'ongoing';
            } elseif ($statusRaw === 'dissolved') {
                $status = 'dissolved';
            }

            $adoptionRaw = isset($map['adoption']) ? strtolower(trim((string) ($row[$map['adoption']] ?? ''))) : '';
            $with_adopted = false;
            $with_replicated = false;
            if (str_contains($adoptionRaw, 'adopt')) {
                $with_adopted = true;
            } elseif (str_contains($adoptionRaw, 'replic')) {
                $with_replicated = true;
            }

            $toBool = function ($v) {
                $v = strtolower(trim((string) ($v ?? '')));
                if ($v === 'true' || $v === '1' || $v === 'yes') {
                    return true;
                }
                return false;
            };

            $with_expr = isset($map['with_expr']) ? $toBool($row[$map['with_expr']]) : false;
            $with_moa = isset($map['with_moa']) ? $toBool($row[$map['with_moa']]) : false;
            $with_res = isset($map['with_res']) ? $toBool($row[$map['with_res']]) : false;
            $included_aip = isset($map['included_aip']) ? $toBool($row[$map['included_aip']]) : false;

            $year_of_moa = null;
            if (isset($map['year_of_moa'])) {
                $val = trim((string) ($row[$map['year_of_moa']] ?? ''));
                $year_of_moa = $val !== '' ? (int) $val : null;
            }

            $year_of_resolution = null;
            if (isset($map['year_of_resolution'])) {
                $val = trim((string) ($row[$map['year_of_resolution']] ?? ''));
                $year_of_resolution = $val !== '' ? (int) $val : null;
            }

            $identity = [
                'region_id' => $region->id,
                'title' => $title,
                'province' => $province ?: null,
                'municipality' => $municipality ?: null,
            ];

            $payload = [
                'status' => $status,
                'with_expr' => $with_expr,
                'with_moa' => $with_moa,
                'year_of_moa' => $with_moa ? $year_of_moa : null,
                'with_res' => $with_res,
                'year_of_resolution' => $with_res ? $year_of_resolution : null,
                'included_aip' => $included_aip,
                'with_adopted' => $with_adopted,
                'with_replicated' => $with_replicated,
                'updatedby' => $actorName,
            ];

            $existing = RegionItem::query()->where($identity)->first();
            if ($existing) {
                $existing->update($payload);
                $updated++;
            } else {
                $payload = array_merge($identity, $payload, ['createdby' => $actorName]);
                RegionItem::query()->create($payload);
                $created++;
            }
        }

        $message = "Force-imported file: created={$created}, updated={$updated}, skipped={$skipped}.";
        if ($warnings) {
            session()->flash('masterdata_import_warnings', $warnings);
        }

        return redirect()->route('masterdata.index', ['tab' => 'overview'])->with('status', $message);
    }

    public function updatesPanel(Request $request): ViewContract
    {
        $actorName = $this->resolveActorName();
        $this->ensureDefaultRegions($actorName);
        $this->backfillAuditNames($actorName);

        $regions = Region::query()
            ->withCount('items')
            ->get()
            ->sortBy(fn (Region $region) => MasterDataRegionCatalog::orderOf($region->name))
            ->values();

        $allRegionItems = RegionItem::query()
            ->with('region:id,name')
            ->latest('updated_at')
            ->get();

        return view('dashboard.maincomponents.partials.masterdata_updates_panel', [
            'regions' => $regions,
            ...$this->buildUpdatesPanelData($request, $regions, $allRegionItems),
        ]);
    }

    public function importGoogleSheet(Request $request, RegionSheetImportService $importer): RedirectResponse
    {
        $validated = $request->validate([
            'google_sheet_url' => ['nullable', 'url'],
            'stored_excel' => ['nullable', 'string'],
        ]);

        try {
            $actorName = $this->resolveActorName();
            $this->ensureDefaultRegions($actorName);
            $storedFilename = null;

            if (!empty($validated['google_sheet_url'])) {
                $storedFilename = $this->downloadGoogleSheetToStorage($validated['google_sheet_url']);
            } elseif (!empty($validated['stored_excel'])) {
                $storedFilename = basename($validated['stored_excel']);
            } else {
                $storedFilename = $this->resolveCurrentGoogleSheetFile();
            }

            if (!$storedFilename) {
                return redirect()->route('masterdata.index', ['tab' => 'overview'])->with('error', 'No Google Sheet source was found to import.');
            }

            $result = $importer->importFromStoredExcel($storedFilename, $actorName);

            return redirect()->route('masterdata.index', ['tab' => 'overview'])->with(
                'status',
                'Imported ' . $result['items_count'] . ' sheet rows across ' . $result['regions_count'] . ' regions from ' . $result['source'] . '. Added ' . $result['added_count'] . ' new STs and updated ' . $result['updated_count'] . ' existing STs.'
            );
        } catch (\Throwable $e) {
            return redirect()->route('masterdata.index', ['tab' => 'overview'])->with('error', $e->getMessage());
        }
    }

    public function storeRegion(Request $request): RedirectResponse
    {
        return redirect()
            ->route('masterdata.index', ['tab' => 'overview'])
            ->with('error', 'Regions are fixed by office and cannot be added manually.');
    }

    public function storeRegionItem(Request $request): RedirectResponse|JsonResponse
    {
        $actorName = $this->resolveActorName();
        $this->ensureDefaultRegions($actorName);

        $validated = $this->validateRegionItem($request);
        $region = Region::query()->findOrFail($validated['region_id']);

        $regionItem = RegionItem::query()->create($this->buildRegionItemPayload($validated, $actorName));

        if ($request->ajax()) {
            return $this->ajaxUpdatesPanelResponse(
                $request,
                $this->buildRegionItemNotification('added', $regionItem),
                $region->name
            );
        }

        return redirect()
            ->route('masterdata.index', $this->buildUpdateRedirectParams($request, $region->name))
            ->with('status', $this->buildRegionItemNotification('added', $regionItem));
    }

    public function updateRegionItem(Request $request, RegionItem $regionItem): RedirectResponse|JsonResponse
    {
        $actorName = $this->resolveActorName();
        $validated = $this->validateRegionItem($request);

        $regionItem->loadMissing('region:id,name');
        $originalAttachmentIdentity = $this->buildRegionItemAttachmentIdentity($regionItem);

        $regionItem->update($this->buildRegionItemPayload($validated, $actorName, $regionItem));
        $regionItem->load('region:id,name');

        $this->syncRegionItemAttachmentIdentity($originalAttachmentIdentity, $regionItem);

        $regionName = $regionItem->fresh('region')?->region?->name ?? Region::query()->find($validated['region_id'])?->name;

        if ($request->ajax()) {
            return $this->ajaxUpdatesPanelResponse(
                $request,
                $this->buildRegionItemNotification('updated', $regionItem->fresh()),
                $regionName
            );
        }

        return redirect()
            ->route('masterdata.index', $this->buildUpdateRedirectParams($request, $regionName))
            ->with('status', $this->buildRegionItemNotification('updated', $regionItem->fresh()));
    }

    public function destroyRegion(Region $region): RedirectResponse
    {
        return redirect()
            ->route('masterdata.index', ['tab' => 'overview'])
            ->with('error', 'Regions are fixed by office and cannot be deleted.');
    }

    public function destroyRegionItem(Request $request, RegionItem $regionItem): RedirectResponse|JsonResponse
    {
        $regionName = $regionItem->region?->name;
        $message = $this->buildRegionItemNotification('deleted', $regionItem);
        $regionItem->delete();

        if ($request->ajax()) {
            return $this->ajaxUpdatesPanelResponse($request, $message, $regionName);
        }

        return redirect()
            ->route('masterdata.index', $this->buildUpdateRedirectParams($request, $regionName))
            ->with('status', $message);
    }

    private function ajaxUpdatesPanelResponse(Request $request, string $message, ?string $regionName)
    {
        $params = $this->buildUpdateRedirectParams($request, $regionName);
        $panelRequest = Request::create(route('masterdata.updates-panel'), 'GET', $params);
        $panelRequest->setUserResolver($request->getUserResolver());
        $panelRequest->setRouteResolver($request->getRouteResolver());

        $html = $this->updatesPanel($panelRequest)->render();

        return response()->json([
            'html' => $html,
            'message' => $message,
            'url' => route('masterdata.index', $params),
        ]);
    }

    private function buildUpdatesPanelData(Request $request, Collection $regions, Collection $allRegionItems): array
    {
        $selectedRegionName = MasterDataRegionCatalog::normalize($request->query('region_filter'));
        if (!$selectedRegionName) {
            $selectedRegionName = $regions->first()?->name;
        }

        $selectedRegion = $regions->firstWhere('name', $selectedRegionName);
        $selectedProvince = trim((string) $request->query('province_filter', ''));
        $selectedMunicipality = trim((string) $request->query('municipality_filter', ''));

        $selectedRegionItems = $selectedRegion
            ? $allRegionItems
                ->where('region_id', $selectedRegion->id)
                ->sortBy([
                    ['title', 'asc'],
                    ['province', 'asc'],
                    ['municipality', 'asc'],
                ])
                ->values()
            : collect();

        $provinceOptions = $selectedRegionItems
            ->pluck('province')
            ->filter(fn ($value) => filled($value))
            ->unique()
            ->sort()
            ->values();

        if ($selectedProvince !== '' && !$provinceOptions->contains($selectedProvince)) {
            $selectedProvince = '';
        }

        $citySourceItems = $selectedProvince !== ''
            ? $selectedRegionItems->filter(fn (RegionItem $item) => (string) $item->province === $selectedProvince)
            : $selectedRegionItems;

        $municipalityOptions = $citySourceItems
            ->pluck('municipality')
            ->filter(fn ($value) => filled($value))
            ->unique()
            ->sort()
            ->values();

        if ($selectedMunicipality !== '' && !$municipalityOptions->contains($selectedMunicipality)) {
            $selectedMunicipality = '';
        }

        $filteredRegionItems = $selectedRegionItems
            ->when($selectedProvince !== '', fn (Collection $items) => $items->filter(fn (RegionItem $item) => (string) $item->province === $selectedProvince))
            ->when($selectedMunicipality !== '', fn (Collection $items) => $items->filter(fn (RegionItem $item) => (string) $item->municipality === $selectedMunicipality))
            ->values();

        $requestedPage = max(1, (int) $request->query('page', 1));
        $perPage = 10;
        $lastPage = max(1, (int) ceil(max(1, $filteredRegionItems->count()) / $perPage));
        $currentPage = min($requestedPage, $lastPage);
        $pagedItems = $filteredRegionItems->slice(($currentPage - 1) * $perPage, $perPage)->values();
        $regionItems = new LengthAwarePaginator(
            $pagedItems,
            $filteredRegionItems->count(),
            $perPage,
            $currentPage,
            [
                'path' => route('masterdata.index'),
                'query' => array_merge($request->query(), ['tab' => 'updates', 'page' => $currentPage]),
            ]
        );

        $attachmentsByItem = $this->buildAttachmentMapForRegionItems($pagedItems);

        $socialTechnologyTitles = SocialTechnologyTitle::query()
            ->orderBy('social_technology')
            ->pluck('social_technology')
            ->values();

        return [
            'regionItems' => $regionItems,
            'attachmentsByItem' => $attachmentsByItem,
            'selectedRegion' => $selectedRegion,
            'selectedRegionName' => $selectedRegionName,
            'selectedProvince' => $selectedProvince,
            'selectedMunicipality' => $selectedMunicipality,
            'provinceOptions' => $provinceOptions,
            'municipalityOptions' => $municipalityOptions,
            'socialTechnologyTitles' => $socialTechnologyTitles,
        ];
    }

    private function buildUpdateRedirectParams(Request $request, ?string $regionName): array
    {
        $params = [
            'tab' => 'updates',
            'region_filter' => $regionName,
        ];

        $province = trim((string) ($request->input('return_province_filter', $request->query('province_filter', ''))));
        $municipality = trim((string) ($request->input('return_municipality_filter', $request->query('municipality_filter', ''))));
        $page = (int) ($request->input('return_page', $request->query('page', 1)));

        if ($province !== '') {
            $params['province_filter'] = $province;
        }
        if ($municipality !== '') {
            $params['municipality_filter'] = $municipality;
        }
        if ($page > 1) {
            $params['page'] = $page;
        }

        return $params;
    }

    private function buildRegionItemNotification(string $action, RegionItem $regionItem): string
    {
        $segments = [trim((string) $regionItem->title)];

        if (filled($regionItem->province)) {
            $segments[] = trim((string) $regionItem->province);
        }

        if (filled($regionItem->municipality)) {
            $segments[] = trim((string) $regionItem->municipality);
        }

        return 'ST item ' . $action . ': ' . implode(' / ', array_filter($segments));
    }

    private function buildAttachmentMapForRegionItems(Collection $items): array
    {
        if ($items->isEmpty()) {
            return [];
        }

        $items = $items->values();
        $items->loadMissing('region:id,name');

        $attachmentsQuery = StsAttachment::query();
        $attachmentsQuery->where(function ($query) use ($items) {
            foreach ($items as $item) {
                $identity = $this->buildRegionItemAttachmentIdentity($item);

                $query->orWhere(function ($subQuery) use ($identity) {
                    $subQuery->where('region', $identity['region'])
                        ->where('title', $identity['title']);

                    $this->whereAttachmentColumnMatches($subQuery, 'province', $identity['province']);
                    $this->whereAttachmentColumnMatches($subQuery, 'municipality', $identity['municipality']);
                    $this->whereAttachmentColumnMatches($subQuery, 'year_of_moa', $identity['year_of_moa']);
                });
            }
        });

        $attachments = $attachmentsQuery->get();
        if ($attachments->isEmpty()) {
            return [];
        }

        $userNames = [];
        $userIds = $attachments->pluck('created_by')->filter()->unique()->values();
        if ($userIds->isNotEmpty()) {
            $userNames = User::query()
                ->whereIn('user_id', $userIds)
                ->pluck('name', 'user_id')
                ->toArray();
        }

        $attachmentMap = [];
        foreach ($attachments as $attachment) {
            $identity = $this->buildAttachmentIdentity(
                $attachment->region,
                $attachment->province,
                $attachment->municipality,
                $attachment->title,
                $attachment->year_of_moa,
            );
            $key = $this->buildAttachmentIdentityKey($identity);

            if (!isset($attachmentMap[$key]) || $attachment->id > $attachmentMap[$key]['id']) {
                $entry = [
                    'id' => $attachment->id,
                    'action' => $attachment->action,
                    'url' => null,
                    'uploaded_by' => $userNames[$attachment->created_by] ?? $attachment->created_by,
                ];

                if ($attachment->action === 'added') {
                    $entry['url'] = route('sts.attachments.show', $attachment->id);
                }

                $attachmentMap[$key] = $entry;
            }
        }

        $attachmentsByItem = [];
        foreach ($items as $item) {
            $identity = $this->buildRegionItemAttachmentIdentity($item);
            $key = $this->buildAttachmentIdentityKey($identity);

            if (!isset($attachmentMap[$key])) {
                continue;
            }

            $entry = $attachmentMap[$key];
            if ($entry['action'] !== 'added' || empty($entry['url'])) {
                continue;
            }

            $attachmentsByItem[$item->id] = [
                'id' => $entry['id'],
                'url' => $entry['url'],
                'uploaded_by' => $entry['uploaded_by'],
            ];
        }

        return $attachmentsByItem;
    }

    private function syncRegionItemAttachmentIdentity(array $originalIdentity, RegionItem $regionItem): void
    {
        $updatedIdentity = $this->buildRegionItemAttachmentIdentity($regionItem);

        if ($this->buildAttachmentIdentityKey($originalIdentity) === $this->buildAttachmentIdentityKey($updatedIdentity)) {
            return;
        }

        $query = StsAttachment::query()
            ->where('region', $originalIdentity['region'])
            ->where('title', $originalIdentity['title']);

        $this->whereAttachmentColumnMatches($query, 'province', $originalIdentity['province']);
        $this->whereAttachmentColumnMatches($query, 'municipality', $originalIdentity['municipality']);
        $this->whereAttachmentColumnMatches($query, 'year_of_moa', $originalIdentity['year_of_moa']);

        $query->update([
            'region' => $updatedIdentity['region'],
            'province' => $updatedIdentity['province'],
            'municipality' => $updatedIdentity['municipality'],
            'title' => $updatedIdentity['title'],
            'year_of_moa' => $updatedIdentity['year_of_moa'],
        ]);
    }

    private function buildRegionItemAttachmentIdentity(RegionItem $regionItem): array
    {
        return $this->buildAttachmentIdentity(
            $regionItem->region?->name,
            $regionItem->province,
            $regionItem->municipality,
            $regionItem->title,
            $regionItem->year_of_moa,
        );
    }

    private function buildAttachmentIdentity(
        ?string $region,
        mixed $province,
        mixed $municipality,
        mixed $title,
        mixed $yearOfMoa,
    ): array {
        return [
            'region' => trim((string) $region),
            'province' => trim((string) ($province ?? '')),
            'municipality' => trim((string) ($municipality ?? '')),
            'title' => trim((string) ($title ?? '')),
            'year_of_moa' => trim((string) ($yearOfMoa ?? '')),
        ];
    }

    private function buildAttachmentIdentityKey(array $identity): string
    {
        return implode('|', [
            $identity['region'],
            $identity['province'],
            $identity['municipality'],
            $identity['title'],
            $identity['year_of_moa'],
        ]);
    }

    private function whereAttachmentColumnMatches($query, string $column, string $value): void
    {
        if ($value === '') {
            $query->where(function ($nestedQuery) use ($column) {
                $nestedQuery->whereNull($column)->orWhere($column, '');
            });

            return;
        }

        $query->where($column, $value);
    }

    private function validateRegionItem(Request $request): array
    {
        return $request->validate([
            'region_id' => ['required', 'exists:regions,id'],
            'title' => ['required', 'string', 'max:255', 'exists:social_technology_titles,social_technology'],
            'province' => ['nullable', 'string', 'max:255'],
            'municipality' => ['nullable', 'string', 'max:255'],
            'with_expr' => ['nullable', 'boolean'],
            'with_moa' => ['nullable', 'boolean'],
            'year_of_moa' => ['nullable', 'integer', 'digits:4', 'min:1900', 'max:2100'],
            'with_res' => ['nullable', 'boolean'],
            'year_of_resolution' => ['nullable', 'integer', 'digits:4', 'min:1900', 'max:2100'],
            'included_aip' => ['nullable', 'boolean'],
            'adoption_status' => ['nullable', 'in:none,adopted,replicated'],
            'status' => ['nullable', 'in:ongoing,dissolved'],
        ]);
    }

    private function buildRegionItemPayload(array $validated, string $actorName, ?RegionItem $existingItem = null): array
    {
        $adoptionStatus = $validated['adoption_status'] ?? 'none';

        $withMoa = (bool) ($validated['with_moa'] ?? false);
        $withResolution = (bool) ($validated['with_res'] ?? false);

        return [
            'region_id' => $validated['region_id'],
            'title' => trim($validated['title']),
            'province' => trim((string) ($validated['province'] ?? '')) ?: null,
            'municipality' => trim((string) ($validated['municipality'] ?? '')) ?: null,
            'with_expr' => (bool) ($validated['with_expr'] ?? false),
            'with_moa' => $withMoa,
            'year_of_moa' => $withMoa ? ($validated['year_of_moa'] ?? null) : null,
            'with_res' => $withResolution,
            'year_of_resolution' => $withResolution ? ($validated['year_of_resolution'] ?? null) : null,
            'included_aip' => (bool) ($validated['included_aip'] ?? false),
            'with_adopted' => $adoptionStatus === 'adopted',
            'with_replicated' => $adoptionStatus === 'replicated',
            'status' => $validated['status'] ?? null,
            'createdby' => $existingItem?->createdby ?: $actorName,
            'updatedby' => $actorName,
        ];
    }

    private function buildOverview(Collection $regions, Collection $regionItems): array
    {
        $statusCounts = [
            'Ongoing' => 0,
            'Dissolved' => 0,
            'Unspecified' => 0,
        ];

        foreach ($regionItems as $item) {
            $status = Str::lower((string) $item->status);
            if ($status === 'ongoing') {
                $statusCounts['Ongoing']++;
            } elseif ($status === 'dissolved') {
                $statusCounts['Dissolved']++;
            } else {
                $statusCounts['Unspecified']++;
            }
        }

        $yearCounts = $regionItems
            ->filter(fn (RegionItem $item) => !empty($item->year_of_moa))
            ->groupBy(fn (RegionItem $item) => (string) $item->year_of_moa)
            ->map(fn (Collection $items) => $items->count())
            ->sortKeys();

        $adoptionCounts = [
            'Adopted' => $regionItems->where('with_adopted', true)->count(),
            'Replicated' => $regionItems->where('with_replicated', true)->count(),
        ];

        $resolutionCountsByRegion = $regions
            ->map(function (Region $region) use ($regionItems) {
                return [
                    'label' => $region->name,
                    'count' => $regionItems
                        ->where('region_id', $region->id)
                        ->where('with_res', true)
                        ->count(),
                ];
            })
            ->values();

        $updatedByCounts = $regionItems
            ->filter(fn (RegionItem $item) => filled($item->updatedby))
            ->groupBy('updatedby')
            ->map(fn (Collection $items) => $items->count())
            ->sortDesc()
            ->take(8);

        $recentUpdates = $regionItems
            ->sortByDesc('updated_at')
            ->take(10)
            ->values();

        return [
            'total_regions' => $regions->count(),
            'total_items' => $regionItems->count(),
            'with_moa' => $regionItems->where('with_moa', true)->count(),
            'with_resolution' => $regionItems->where('with_res', true)->count(),
            'status_counts' => $statusCounts,
            'region_counts' => $regions->map(fn (Region $region) => [
                'label' => $region->name,
                'count' => (int) $region->items_count,
            ])->values()->all(),
            'year_counts' => [
                'labels' => $yearCounts->keys()->values()->all(),
                'values' => $yearCounts->values()->all(),
            ],
            'adoption_counts' => $adoptionCounts,
            'resolution_counts_by_region' => [
                'labels' => $resolutionCountsByRegion->pluck('label')->all(),
                'values' => $resolutionCountsByRegion->pluck('count')->all(),
            ],
            'updated_by_counts' => [
                'labels' => $updatedByCounts->keys()->values()->all(),
                'values' => $updatedByCounts->values()->all(),
            ],
            'recent_updates' => $recentUpdates,
            'last_updated_at' => $recentUpdates->first()?->updated_at,
        ];
    }

    private function ensureDefaultRegions(string $actorName): void
    {
        foreach (MasterDataRegionCatalog::all() as $regionName) {
            $region = Region::query()->firstOrCreate(
                ['name' => $regionName],
                ['createdby' => $actorName, 'updatedby' => $actorName]
            );

            $updates = [];
            if (!$region->createdby) {
                $updates['createdby'] = $actorName;
            }
            if (!$region->updatedby) {
                $updates['updatedby'] = $actorName;
            }

            if ($updates !== []) {
                $region->forceFill($updates)->save();
            }
        }
    }

    private function backfillAuditNames(string $actorName): void
    {
        Region::query()
            ->where(fn ($query) => $query->whereNull('createdby')->orWhere('createdby', ''))
            ->update(['createdby' => $actorName]);

        Region::query()
            ->where(fn ($query) => $query->whereNull('updatedby')->orWhere('updatedby', ''))
            ->update(['updatedby' => $actorName]);

        RegionItem::query()
            ->where(fn ($query) => $query->whereNull('createdby')->orWhere('createdby', ''))
            ->update(['createdby' => $actorName]);

        RegionItem::query()
            ->where(fn ($query) => $query->whereNull('updatedby')->orWhere('updatedby', ''))
            ->update(['updatedby' => $actorName]);
    }

    private function resolveActorName(): string
    {
        if (!Auth::check()) {
            return 'System';
        }

        $user = Auth::user();
        $name = trim((string) ($user->name ?? ''));
        if ($name !== '') {
            return $name;
        }

        $parts = array_values(array_filter([
            trim((string) ($user->firstname ?? '')),
            trim((string) ($user->middlename ?? '')),
            trim((string) ($user->lastname ?? '')),
        ]));

        if ($parts !== []) {
            return implode(' ', $parts);
        }

        return (string) ($user->user_id ?? $user->id ?? 'System');
    }

    private function resolveCurrentGoogleSheetFile(): ?string
    {
        $excelDir = storage_path('app/excels');
        $selectLogs = Selectdocslogs::query()->orderByDesc('updated_at')->get();

        foreach ($selectLogs as $log) {
            $filename = trim((string) $log->excelname);
            if ($filename === '' || !str_starts_with($filename, 'gsheet_')) {
                continue;
            }
            $fullPath = $excelDir . DIRECTORY_SEPARATOR . $filename;
            if (file_exists($fullPath) && is_readable($fullPath)) {
                return $filename;
            }
        }

        $files = glob($excelDir . DIRECTORY_SEPARATOR . 'gsheet_*.xlsx') ?: [];
        if (empty($files)) {
            return null;
        }

        usort($files, fn ($a, $b) => filemtime($b) <=> filemtime($a));
        return basename($files[0]);
    }

    private function downloadGoogleSheetToStorage(string $googleSheetUrl): string
    {
        if (!preg_match('#/spreadsheets/d/([a-zA-Z0-9-_]+)#', $googleSheetUrl, $matches)) {
            throw new \RuntimeException('Invalid Google Sheets URL format.');
        }

        $spreadsheetId = $matches[1];
        $exportUrl = 'https://docs.google.com/spreadsheets/d/' . $spreadsheetId . '/export?format=xlsx';

        try {
            $response = Http::get($exportUrl);
        } catch (\Throwable $e) {
            throw new \RuntimeException('Error downloading Google Sheet. Please verify sharing permissions.');
        }

        if (!$response->ok()) {
            $status = $response->status();
            if ($status === 401 || $status === 403) {
                throw new \RuntimeException('The Google Sheet is not accessible. Share it as "Anyone with the link".');
            }
            throw new \RuntimeException('Failed to download Google Sheet (HTTP ' . $status . ').');
        }

        $excelDir = storage_path('app/excels');
        if (!is_dir($excelDir)) {
            mkdir($excelDir, 0775, true);
        }

        $storedFilename = 'gsheet_' . $spreadsheetId . '_' . time() . '.xlsx';
        $fullPath = $excelDir . DIRECTORY_SEPARATOR . $storedFilename;
        file_put_contents($fullPath, $response->body());

        $lastDocno = Uploadlog::max('docno');
        $nextNumber = $lastDocno ? ((int) $lastDocno) + 1 : 1;
        $nextDocno = str_pad($nextNumber, 7, '0', STR_PAD_LEFT);
        $userId = Auth::check() ? (Auth::user()->user_id ?? Auth::user()->id) : null;

        Uploadlog::query()->create([
            'createdby' => $userId,
            'excelname' => $storedFilename,
            'docno' => $nextDocno,
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        Selectdocslogs::query()->create([
            'createdby' => $userId,
            'excelname' => $storedFilename,
            'actionlogs' => 'import-to-masterdata',
            'docselected' => $nextDocno,
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        session(['base_excel' => $storedFilename]);

        return $storedFilename;
    }
}