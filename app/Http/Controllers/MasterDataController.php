<?php

namespace App\Http\Controllers;

use App\Models\Region;
use App\Models\RegionItem;
use App\Models\RegionItemHistory;
use App\Models\Selectdocslogs;
use App\Models\StsAttachment;
use App\Models\Uploadlog;
use App\Models\User;
use App\Services\RegionSheetImportService;
use App\Support\MasterDataRegionCatalog;
use Carbon\Carbon;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Http;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Str;
use Illuminate\View\View as ViewContract;
use Illuminate\View\View;
use App\Models\SocialTechnologyTitle;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
use PhpOffice\PhpSpreadsheet\IOFactory;
use PhpOffice\PhpSpreadsheet\Shared\Date as SpreadsheetDate;

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
            'Inactive Status',
            'Inactive Remarks',
        ];

        $sheet->fromArray($headers, null, 'A1');

        $row = 2;
        foreach ($items as $item) {
            $status = Str::lower((string) $item->status);
            if ($status === 'ongoing') {
                $statusLabel = 'Ongoing';
            } elseif (in_array($status, ['inactive', 'dissolved'], true)) {
                $statusLabel = 'Inactive';
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
            $sheet->setCellValue('M' . $row, $item->inactive_status ?? '');
            $sheet->setCellValue('N' . $row, $item->inactive_remarks ?? '');

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

            $headerIndex = 1;
            $headerRow = array_map('trim', (array) $rows[$headerIndex]);

            $map = [];
            foreach ($headerRow as $col => $val) {
                $norm = strtolower($val);
                if (str_contains($norm, 'region')) {
                    $map['region'] = strtoupper($col);
                } elseif (str_contains($norm, 'title')) {
                    $map['title'] = strtoupper($col);
                } elseif (str_contains($norm, 'remark') || str_contains($norm, 'remarks')) {
                    if (!isset($map['inactive_remarks'])) {
                        $map['inactive_remarks'] = strtoupper($col);
                    }
                } elseif (str_contains($norm, 'inactive')) {
                    if (!isset($map['inactive_status'])) {
                        $map['inactive_status'] = strtoupper($col);
                    }
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
                        } elseif (str_contains($norm, 'remark') || str_contains($norm, 'remarks')) {
                            if (!isset($map['inactive_remarks'])) {
                                $map['inactive_remarks'] = strtoupper($col);
                            }
                        } elseif (str_contains($norm, 'inactive')) {
                            if (!isset($map['inactive_status'])) {
                                $map['inactive_status'] = strtoupper($col);
                            }
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

                    $inactiveCols = [];
                    foreach ($headerRow as $col => $val) {
                        if (is_string($val) && stripos($val, 'inactive') !== false) {
                            $inactiveCols[] = strtoupper($col);
                        }
                    }
                    if (count($inactiveCols) >= 2) {
                        if (!isset($map['inactive_status'])) {
                            $map['inactive_status'] = $inactiveCols[0];
                        }
                        if (!isset($map['inactive_remarks'])) {
                            $map['inactive_remarks'] = $inactiveCols[1];
                        }
                    } elseif (count($inactiveCols) === 1) {
                        if (!isset($map['inactive_status'])) {
                            $map['inactive_status'] = $inactiveCols[0];
                        }
                        if (!isset($map['inactive_remarks'])) {
                            foreach ($headerRow as $col => $val) {
                                if (is_string($val) && stripos($val, 'remark') !== false) {
                                    $map['inactive_remarks'] = strtoupper($col);
                                    break;
                                }
                            }
                        }
                    }
                    if (!isset($map['status'])) {
                        foreach ($headerRow as $col => $val) {
                            if (is_string($val) && stripos($val, 'status') !== false) {
                                $map['status'] = strtoupper($col);
                                break;
                            }
                        }
                    }
                    if (!isset($map['status'])) {
                        foreach ($headerRow as $col => $val) {
                            if (is_string($val) && stripos($val, 'status') !== false) {
                                $map['status'] = strtoupper($col);
                                break;
                            }
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
        $statusIssues = [];

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
        foreach ($sampleRows as $i => $r) {
            $rowNum = $headerIndex + $i + 1;
            $debugSample['rows'][$i]['inactive_status_raw'] = isset($map['inactive_status']) ? ($r[$map['inactive_status']] ?? '') : null;
            $debugSample['rows'][$i]['inactive_remarks_raw'] = isset($map['inactive_remarks']) ? ($r[$map['inactive_remarks']] ?? '') : null;
        }
        session()->flash('masterdata_import_debug', $debugSample);

        $inactive_status = null;
        $inactive_remarks = null;

        foreach (array_slice($rows, $headerIndex) as $idx => $row) {
            $sheetRowNumber = $headerIndex + $idx + 1; 
            $regionName = isset($map['region']) ? trim((string) ($row[$map['region']] ?? '')) : '';
            $title = isset($map['title']) ? trim((string) ($row[$map['title']] ?? '')) : '';

            if ($title === '') {
                $skipped++;
                $warnings[] = "Row {$sheetRowNumber}: title empty, skipped.";
                continue;
            }

            $province = isset($map['province']) ? trim((string) ($row[$map['province']] ?? '')) : null;
            $municipality = isset($map['municipality']) ? trim((string) ($row[$map['municipality']] ?? '')) : null;

                $inactive_status = isset($map['inactive_status']) ? trim((string) ($row[$map['inactive_status']] ?? '')) : null;
                $inactive_remarks_raw = isset($map['inactive_remarks']) ? ($row[$map['inactive_remarks']] ?? null) : null;
                if ($inactive_remarks_raw !== null) {
                    $inr = is_scalar($inactive_remarks_raw) ? (string) $inactive_remarks_raw : '';
                    $inr = preg_replace('/[\x00-\x1F\x7F\xA0]+/u', ' ', $inr);
                    $inr = trim(preg_replace('/\s+/u', ' ', $inr));
                    $inactive_remarks = $inr === '' ? null : $inr;
                } else {
                    $inactive_remarks = null;
                }

            $inactive_status = isset($map['inactive_status']) ? trim((string) ($row[$map['inactive_status']] ?? '')) : null;
            $inactive_remarks_raw = isset($map['inactive_remarks']) ? ($row[$map['inactive_remarks']] ?? null) : null;
            if ($inactive_remarks_raw !== null) {
                $inr = is_scalar($inactive_remarks_raw) ? (string) $inactive_remarks_raw : '';
                $inr = preg_replace('/[\x00-\x1F\x7F\xA0]+/u', ' ', $inr);
                $inr = trim(preg_replace('/\s+/u', ' ', $inr));
                $inactive_remarks = $inr === '' ? null : $inr;
            } else {
                $inactive_remarks = null;
            }

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

            $titleExists = SocialTechnologyTitle::query()->whereRaw('LOWER(social_technology) = ?', [strtolower($title)])->exists();
            if (!$titleExists) {
                $skipped++;
                $warnings[] = "Row {$sheetRowNumber}: title not found ('{$title}')";
                continue;
            }

            $statusRaw = '';
            if (isset($map['status'])) {
                $rawVal = $row[$map['status']] ?? '';
                $rawStr = is_scalar($rawVal) ? (string) $rawVal : '';
                $rawStr = preg_replace('/[\x00-\x1F\x7F\xA0]+/u', ' ', $rawStr);
                $statusRaw = trim(preg_replace('/\s+/u', ' ', $rawStr));
            }
            $status = null;
            if ($statusRaw !== '') {
                $snorm = strtolower($statusRaw);
                if (str_contains($snorm, 'ongoing') || $snorm === 'on going') {
                    $status = 'ongoing';
                } elseif (str_contains($snorm, 'dissolved') || str_contains($snorm, 'inactive') || str_contains($snorm, 'completed')) {
                    $status = 'dissolved';
                } else {
                    // Unknown or free-text status values are not valid for the
                    // DB enum (allowed: 'ongoing','dissolved'). Store null
                    // and record the raw value for review instead of trying
                    // to persist arbitrary strings which causes SQL truncation.
                    $status = null;
                }
            }
            if ($status === null && $statusRaw !== '') {
                $statusIssues[] = ['sheet_row' => $sheetRowNumber, 'raw_status' => $statusRaw];
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
                $raw = $row[$map['year_of_moa']] ?? null;
                $year_of_moa = null;
                if ($raw !== null && $raw !== '') {
                    if ($raw instanceof \DateTimeInterface) {
                        $y = (int) $raw->format('Y');
                        $year_of_moa = ($y >= 1900 && $y <= 2100) ? $y : null;
                    } elseif (is_numeric($raw)) {
                        $n = (int) $raw;
                        if ($n >= 1900 && $n <= 2100) {
                            $year_of_moa = $n;
                        } else {
                            try {
                                $dt = SpreadsheetDate::excelToDateTimeObject($raw);
                                $y = (int) $dt->format('Y');
                                $year_of_moa = ($y >= 1900 && $y <= 2100) ? $y : null;
                            } catch (\Throwable $e) {
                                if (preg_match('/\b(19|20)\d{2}\b/', (string) $raw, $m)) {
                                    $year_of_moa = (int) $m[0];
                                }
                            }
                        }
                    } else {
                        if (preg_match('/\b(19|20)\d{2}\b/', (string) $raw, $m)) {
                            $year_of_moa = (int) $m[0];
                        }
                    }
                }
            }

            $year_of_resolution = null;
            if (isset($map['year_of_resolution'])) {
                $raw = $row[$map['year_of_resolution']] ?? null;
                if ($raw !== null && $raw !== '') {
                    if ($raw instanceof \DateTimeInterface) {
                        $y = (int) $raw->format('Y');
                        $year_of_resolution = ($y >= 1900 && $y <= 2100) ? $y : null;
                    } elseif (is_numeric($raw)) {
                        $n = (int) $raw;
                        if ($n >= 1900 && $n <= 2100) {
                            $year_of_resolution = $n;
                        } else {
                            try {
                                $dt = SpreadsheetDate::excelToDateTimeObject($raw);
                                $y = (int) $dt->format('Y');
                                $year_of_resolution = ($y >= 1900 && $y <= 2100) ? $y : null;
                            } catch (\Throwable $e) {
                                if (preg_match('/\b(19|20)\d{2}\b/', (string) $raw, $m)) {
                                    $year_of_resolution = (int) $m[0];
                                }
                            }
                        }
                    } else {
                        if (preg_match('/\b(19|20)\d{2}\b/', (string) $raw, $m)) {
                            $year_of_resolution = (int) $m[0];
                        }
                    }
                }
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
                'year_of_moa' => $year_of_moa,
                'with_res' => $with_res,
                'year_of_resolution' => $year_of_resolution,
                'included_aip' => $included_aip,
                'with_adopted' => $with_adopted,
                'with_replicated' => $with_replicated,
                'inactive_status' => $inactive_status ?: null,
                'inactive_remarks' => $inactive_remarks ?: null,
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

        try {
            $debug = ['source' => $file->getClientOriginalName() ?? null, 'header_index' => $headerIndex, 'map' => $map, 'status_issues' => $statusIssues];
            $debugPath = storage_path('app/excels/masterdata_import_debug_' . time() . '.json');
            @mkdir(dirname($debugPath), 0777, true);
            file_put_contents($debugPath, json_encode($debug, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES));
            session()->flash('masterdata_import_debug_file', basename($debugPath));
        } catch (\Throwable $e) {
        }

        return redirect()->route('masterdata.index', ['tab' => 'overview'])->with('status', $message);
    }

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

        $headerIndex = 1;
        $headerRow = array_map('trim', (array) $rows[$headerIndex]);

        $map = [];
            foreach ($headerRow as $col => $val) {
                $norm = strtolower($val);
                if (str_contains($norm, 'region')) {
                    $map['region'] = strtoupper($col);
                } elseif (str_contains($norm, 'title')) {
                    $map['title'] = strtoupper($col);
                } elseif (str_contains($norm, 'remark') || str_contains($norm, 'remarks')) {
                    if (!isset($map['inactive_remarks'])) {
                        $map['inactive_remarks'] = strtoupper($col);
                    }
                } elseif (str_contains($norm, 'inactive')) {
                    if (!isset($map['inactive_status'])) {
                        $map['inactive_status'] = strtoupper($col);
                    }
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

        $inactiveCols = [];
        foreach ($headerRow as $col => $val) {
            if (is_string($val) && stripos($val, 'inactive') !== false) {
                $inactiveCols[] = strtoupper($col);
            }
        }
        if (count($inactiveCols) >= 2) {
            if (!isset($map['inactive_status'])) {
                $map['inactive_status'] = $inactiveCols[0];
            }
            if (!isset($map['inactive_remarks'])) {
                $map['inactive_remarks'] = $inactiveCols[1];
            }
        } elseif (count($inactiveCols) === 1) {
            if (!isset($map['inactive_status'])) {
                $map['inactive_status'] = $inactiveCols[0];
            }
            if (!isset($map['inactive_remarks'])) {
                foreach ($headerRow as $col => $val) {
                    if (is_string($val) && stripos($val, 'remark') !== false) {
                        $map['inactive_remarks'] = strtoupper($col);
                        break;
                    }
                }
            }
        }

                    if (isset($map['inactive_status']) && !isset($map['inactive_remarks'])) {
                        $colToIndex = function ($col) {
                            $col = strtoupper($col);
                            $len = strlen($col);
                            $n = 0;
                            for ($i = 0; $i < $len; $i++) {
                                $n = $n * 26 + (ord($col[$i]) - 64);
                            }
                            return $n;
                        };
                        $indexToCol = function ($n) {
                            $s = '';
                            while ($n > 0) {
                                $mod = ($n - 1) % 26;
                                $s = chr(65 + $mod) . $s;
                                $n = (int) (($n - 1) / 26);
                            }
                            return $s;
                        };

                        try {
                            $right = $indexToCol($colToIndex($map['inactive_status']) + 1);
                            $has = false;
                            $maxCheck = min(count($rows), $headerIndex + 20);
                            for ($r = $headerIndex + 1; $r <= $maxCheck; $r++) {
                                $cell = $rows[$r][$right] ?? '';
                                if (is_scalar($cell) && trim((string) $cell) !== '') {
                                    $has = true;
                                    break;
                                }
                            }
                            if ($has) {
                                $map['inactive_remarks'] = $right;
                            }
                        } catch (\Throwable $e) {
                        }
                    }

            if (isset($map['inactive_status']) && !isset($map['inactive_remarks'])) {
                $colToIndex = function ($col) {
                    $col = strtoupper($col);
                    $len = strlen($col);
                    $n = 0;
                    for ($i = 0; $i < $len; $i++) {
                        $n = $n * 26 + (ord($col[$i]) - 64);
                    }
                    return $n;
                };
                $indexToCol = function ($n) {
                    $s = '';
                    while ($n > 0) {
                        $mod = ($n - 1) % 26;
                        $s = chr(65 + $mod) . $s;
                        $n = (int) (($n - 1) / 26);
                    }
                    return $s;
                };

                try {
                    $right = $indexToCol($colToIndex($map['inactive_status']) + 1);
                    $has = false;
                    $maxCheck = min(count($rows), $headerIndex + 20);
                    for ($r = $headerIndex + 1; $r <= $maxCheck; $r++) {
                        $cell = $rows[$r][$right] ?? '';
                        if (is_scalar($cell) && trim((string) $cell) !== '') {
                            $has = true;
                            break;
                        }
                    }
                    if ($has) {
                        $map['inactive_remarks'] = $right;
                    }
                } catch (\Throwable $e) {
                }
            }

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
                        } elseif (str_contains($norm, 'inactive')) {
                            $map['inactive_status'] = strtoupper($col);
                        } elseif (str_contains($norm, 'remark') || str_contains($norm, 'remarks')) {
                            $map['inactive_remarks'] = strtoupper($col);
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
        $statusIssues = [];

        $dataRows = array_slice($rows, $headerIndex);

        $inactive_status = null;
        $inactive_remarks = null;

        foreach ($dataRows as $idx => $row) {
            $sheetRowNumber = $idx + $headerIndex + 1; 

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

            $statusRaw = '';
            if (isset($map['status'])) {
                $rawVal = $row[$map['status']] ?? '';
                $rawStr = is_scalar($rawVal) ? (string) $rawVal : '';
                $rawStr = preg_replace('/[\x00-\x1F\x7F\xA0]+/u', ' ', $rawStr);
                $statusRaw = trim(preg_replace('/\s+/u', ' ', $rawStr));
            }
            $status = null;
            if ($statusRaw !== '') {
                $snorm = strtolower($statusRaw);
                if (str_contains($snorm, 'ongoing') || $snorm === 'on going') {
                    $status = 'ongoing';
                } elseif (str_contains($snorm, 'dissolved') || str_contains($snorm, 'inactive') || str_contains($snorm, 'completed')) {
                    $status = 'dissolved';
                } else {
                    // Unknown status values should not be written into the enum
                    // column. Use null and record the raw value for review.
                    $status = null;
                }
            }
            if ($status === null && $statusRaw !== '') {
                $statusIssues[] = ['sheet_row' => $sheetRowNumber, 'raw_status' => $statusRaw];
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
                $raw = $row[$map['year_of_moa']] ?? null;
                $year_of_moa = null;
                if ($raw !== null && $raw !== '') {
                    if ($raw instanceof \DateTimeInterface) {
                        $y = (int) $raw->format('Y');
                        $year_of_moa = ($y >= 1900 && $y <= 2100) ? $y : null;
                    } elseif (is_numeric($raw)) {
                        $n = (int) $raw;
                        if ($n >= 1900 && $n <= 2100) {
                            $year_of_moa = $n;
                        } else {
                            try {
                                $dt = SpreadsheetDate::excelToDateTimeObject($raw);
                                $y = (int) $dt->format('Y');
                                $year_of_moa = ($y >= 1900 && $y <= 2100) ? $y : null;
                            } catch (\Throwable $e) {
                                if (preg_match('/\b(19|20)\d{2}\b/', (string) $raw, $m)) {
                                    $year_of_moa = (int) $m[0];
                                }
                            }
                        }
                    } else {
                        if (preg_match('/\b(19|20)\d{2}\b/', (string) $raw, $m)) {
                            $year_of_moa = (int) $m[0];
                        }
                    }
                }
            }

            $year_of_resolution = null;
            if (isset($map['year_of_resolution'])) {
                $raw = $row[$map['year_of_resolution']] ?? null;
                if ($raw !== null && $raw !== '') {
                    if ($raw instanceof \DateTimeInterface) {
                        $y = (int) $raw->format('Y');
                        $year_of_resolution = ($y >= 1900 && $y <= 2100) ? $y : null;
                    } elseif (is_numeric($raw)) {
                        $n = (int) $raw;
                        if ($n >= 1900 && $n <= 2100) {
                            $year_of_resolution = $n;
                        } else {
                            try {
                                $dt = SpreadsheetDate::excelToDateTimeObject($raw);
                                $y = (int) $dt->format('Y');
                                $year_of_resolution = ($y >= 1900 && $y <= 2100) ? $y : null;
                            } catch (\Throwable $e) {
                                if (preg_match('/\b(19|20)\d{2}\b/', (string) $raw, $m)) {
                                    $year_of_resolution = (int) $m[0];
                                }
                            }
                        }
                    } else {
                        if (preg_match('/\b(19|20)\d{2}\b/', (string) $raw, $m)) {
                            $year_of_resolution = (int) $m[0];
                        }
                    }
                }
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
                'year_of_moa' => $year_of_moa,
                'with_res' => $with_res,
                'year_of_resolution' => $year_of_resolution,
                'included_aip' => $included_aip,
                'with_adopted' => $with_adopted,
                'with_replicated' => $with_replicated,
                'inactive_status' => $inactive_status ?: null,
                'inactive_remarks' => $inactive_remarks ?: null,
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

        try {
            $debug = ['source' => $file->getClientOriginalName() ?? null, 'header_index' => $headerIndex, 'map' => $map, 'status_issues' => $statusIssues];
            $debugPath = storage_path('app/excels/masterdata_force_import_debug_' . time() . '.json');
            @mkdir(dirname($debugPath), 0777, true);
            file_put_contents($debugPath, json_encode($debug, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES));
            session()->flash('masterdata_force_import_debug_file', basename($debugPath));
        } catch (\Throwable $e) {
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
        $payload = $this->buildRegionItemPayload($validated, $actorName);

        $regionItem = DB::transaction(function () use ($payload, $region, $actorName) {
            $createdItem = RegionItem::query()->create($payload);
            $createdItem->setRelation('region', $region);

            $this->recordRegionItemHistory(
                $createdItem,
                'add',
                $actorName,
                $this->buildRegionItemChangeSet([], $this->buildRegionItemHistorySnapshot($createdItem, $region->name))
            );

            return $createdItem;
        });

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
        $originalSnapshot = $this->buildRegionItemHistorySnapshot($regionItem);
        $originalAttachmentIdentity = $this->buildRegionItemAttachmentIdentity($regionItem);
        $payload = $this->buildRegionItemPayload($validated, $actorName, $regionItem);

        DB::transaction(function () use ($regionItem, $payload, $originalAttachmentIdentity, $originalSnapshot, $actorName) {
            $regionItem->update($payload);
            $regionItem->load('region:id,name');

            $this->syncRegionItemAttachmentIdentity($originalAttachmentIdentity, $regionItem);

            $this->recordRegionItemHistory(
                $regionItem,
                'update',
                $actorName,
                $this->buildRegionItemChangeSet($originalSnapshot, $this->buildRegionItemHistorySnapshot($regionItem))
            );
        });

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
        $actorName = $this->resolveActorName();
        $regionItem->loadMissing('region:id,name');
        $regionName = $regionItem->region?->name;
        $message = $this->buildRegionItemNotification('deleted', $regionItem);

        DB::transaction(function () use ($regionItem, $actorName, $regionName) {
            $this->recordRegionItemHistory(
                $regionItem,
                'delete',
                $actorName,
                $this->buildRegionItemDeleteChangeSet($this->buildRegionItemHistorySnapshot($regionItem, $regionName))
            );

            $regionItem->delete();
        });

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
                'query' => array_merge(array_diff_key($request->query(), ['page' => true, 'history_page' => true]), ['tab' => 'updates', 'page' => $currentPage]),
            ]
        );

        $attachmentsByItem = $this->buildAttachmentMapForRegionItems($pagedItems);

        $socialTechnologyTitles = SocialTechnologyTitle::query()
            ->orderBy('social_technology')
            ->pluck('social_technology')
            ->values();

        $canViewRegionItemHistory = $this->canViewRegionItemHistory();
        $historyDateFrom = trim((string) $request->query('history_date_from', ''));
        $historyDateTo = trim((string) $request->query('history_date_to', ''));
        $showRegionItemHistoryModal = $canViewRegionItemHistory && $request->boolean('history_modal');
        $regionItemHistoryLogs = null;

        if ($canViewRegionItemHistory) {
            $historyQuery = RegionItemHistory::query()->latest('created_at');

            $fromDate = $this->parseRegionItemHistoryDate($historyDateFrom);
            if ($fromDate) {
                $historyQuery->where('created_at', '>=', $fromDate->startOfDay());
                $historyDateFrom = $fromDate->toDateString();
            } else {
                $historyDateFrom = '';
            }

            $toDate = $this->parseRegionItemHistoryDate($historyDateTo);
            if ($toDate) {
                $historyQuery->where('created_at', '<=', $toDate->endOfDay());
                $historyDateTo = $toDate->toDateString();
            } else {
                $historyDateTo = '';
            }

            $historyQueryParams = $request->query();
            unset($historyQueryParams['history_page']);

            $regionItemHistoryLogs = $historyQuery
                ->paginate(10, ['*'], 'history_page', max(1, (int) $request->query('history_page', 1)))
                ->appends(array_merge($historyQueryParams, [
                    'tab' => 'updates',
                    'history_modal' => 1,
                ]));
        }

        $updatesQuery = $request->query();
        unset($updatesQuery['page'], $updatesQuery['history_page']);

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
            'canViewRegionItemHistory' => $canViewRegionItemHistory,
            'historyDateFrom' => $historyDateFrom,
            'historyDateTo' => $historyDateTo,
            'showRegionItemHistoryModal' => $showRegionItemHistoryModal,
            'regionItemHistoryLogs' => $regionItemHistoryLogs,
            'updatesQuery' => $updatesQuery,
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

        $historyDateFrom = trim((string) ($request->input('return_history_date_from', $request->query('history_date_from', ''))));
        $historyDateTo = trim((string) ($request->input('return_history_date_to', $request->query('history_date_to', ''))));
        $historyPage = (int) ($request->input('return_history_page', $request->query('history_page', 1)));
        $historyModal = filter_var(
            $request->input('return_history_modal', $request->query('history_modal', false)),
            FILTER_VALIDATE_BOOLEAN
        );

        if ($historyDateFrom !== '') {
            $params['history_date_from'] = $historyDateFrom;
        }
        if ($historyDateTo !== '') {
            $params['history_date_to'] = $historyDateTo;
        }
        if ($historyModal) {
            $params['history_modal'] = 1;
            if ($historyPage > 1) {
                $params['history_page'] = $historyPage;
            }
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
                ->whereIn('id', $userIds->all())
                ->pluck('name', 'id')
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
            'status' => ['nullable', 'in:ongoing,inactive'],
            'inactive_status' => ['nullable', 'in:pending_document,dissolved'],
            'inactive_remarks' => ['nullable', 'string', 'max:2000'],
        ]);
    }

    private function buildRegionItemPayload(array $validated, string $actorName, ?RegionItem $existingItem = null): array
    {
        $adoptionStatus = $validated['adoption_status'] ?? 'none';

        $withMoa = (bool) ($validated['with_moa'] ?? false);
        $withResolution = (bool) ($validated['with_res'] ?? false);

        $statusVal = $validated['status'] ?? null;
        if ($statusVal === 'inactive') {
            $statusVal = 'dissolved';
        }

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
            'status' => $statusVal ?? null,
            'inactive_status' => $validated['inactive_status'] ?? null,
            'inactive_remarks' => isset($validated['inactive_remarks']) ? trim($validated['inactive_remarks']) : null,
            'createdby' => $existingItem?->createdby ?: $actorName,
            'updatedby' => $actorName,
        ];
    }

    private function canViewRegionItemHistory(): bool
    {
        return in_array(Auth::user()?->usergroup, ['admin', 'sysadmin'], true);
    }

    private function parseRegionItemHistoryDate(string $value): ?Carbon
    {
        if ($value === '') {
            return null;
        }

        try {
            return Carbon::createFromFormat('Y-m-d', $value);
        } catch (\Throwable) {
            return null;
        }
    }

    private function buildRegionItemHistorySnapshot(RegionItem $regionItem, ?string $regionName = null): array
    {
        return [
            'region_name' => trim((string) ($regionName ?? $regionItem->region?->name ?? '')),
            'title' => trim((string) $regionItem->title),
            'province' => $this->normalizeRegionItemHistoryValue($regionItem->province),
            'municipality' => $this->normalizeRegionItemHistoryValue($regionItem->municipality),
            'with_expr' => (bool) $regionItem->with_expr,
            'with_moa' => (bool) $regionItem->with_moa,
            'year_of_moa' => $regionItem->year_of_moa,
            'with_res' => (bool) $regionItem->with_res,
            'year_of_resolution' => $regionItem->year_of_resolution,
            'included_aip' => (bool) $regionItem->included_aip,
            'with_adopted' => (bool) $regionItem->with_adopted,
            'with_replicated' => (bool) $regionItem->with_replicated,
            'status' => $this->normalizeRegionItemHistoryValue($regionItem->status),
            'inactive_status' => $this->normalizeRegionItemHistoryValue($regionItem->inactive_status),
            'inactive_remarks' => $this->normalizeRegionItemHistoryValue($regionItem->inactive_remarks),
        ];
    }

    private function buildRegionItemChangeSet(array $before, array $after): array
    {
        $labels = [
            'region_name' => 'Region',
            'title' => 'ST Title',
            'province' => 'Province',
            'municipality' => 'City / Municipality',
            'with_expr' => 'With Expression of Interest',
            'with_moa' => 'With MOA',
            'year_of_moa' => 'Year of MOA',
            'with_res' => 'With Resolution',
            'year_of_resolution' => 'Year of Resolution',
            'included_aip' => 'Included AIP',
            'with_adopted' => 'Adopted',
            'with_replicated' => 'Replicated',
            'status' => 'Status',
            'inactive_status' => 'Inactive Status',
            'inactive_remarks' => 'Inactive Remarks',
        ];

        $changes = [];

        foreach ($labels as $field => $label) {
            $oldValue = $before[$field] ?? null;
            $newValue = $after[$field] ?? null;

            if ($oldValue === $newValue) {
                continue;
            }

            $changes[] = [
                'field' => $label,
                'from' => $this->formatRegionItemHistoryValue($field, $oldValue),
                'to' => $this->formatRegionItemHistoryValue($field, $newValue),
            ];
        }

        return $changes;
    }

    private function buildRegionItemDeleteChangeSet(array $snapshot): array
    {
        return [
            [
                'field' => 'Deleted Row',
                'from' => 'Active',
                'to' => collect($snapshot)
                    ->map(function (mixed $value, string $field) {
                        $label = match ($field) {
                            'region_name' => 'Region',
                            'title' => 'ST Title',
                            'province' => 'Province',
                            'municipality' => 'City / Municipality',
                            'with_expr' => 'With Expression of Interest',
                            'with_moa' => 'With MOA',
                            'year_of_moa' => 'Year of MOA',
                            'with_res' => 'With Resolution',
                            'year_of_resolution' => 'Year of Resolution',
                            'included_aip' => 'Included AIP',
                            'with_adopted' => 'Adopted',
                            'with_replicated' => 'Replicated',
                            'status' => 'Status',
                            'inactive_status' => 'Inactive Status',
                            'inactive_remarks' => 'Inactive Remarks',
                            default => Str::headline($field),
                        };

                        return $label . ': ' . $this->formatRegionItemHistoryValue($field, $value);
                    })
                    ->implode(', '),
            ],
        ];
    }

    private function normalizeRegionItemHistoryValue(mixed $value): mixed
    {
        if (is_string($value)) {
            $value = trim($value);
            return $value === '' ? null : $value;
        }

        return $value;
    }

    private function formatRegionItemHistoryValue(string $field, mixed $value): string
    {
        if (in_array($field, ['with_expr', 'with_moa', 'with_res', 'included_aip', 'with_adopted', 'with_replicated'], true)) {
            return $value ? 'Yes' : 'No';
        }

        if ($field === 'status') {
            if ($value === null) {
                return 'Blank';
            }

            return in_array($value, ['inactive', 'dissolved'], true)
                ? 'Inactive'
                : Str::headline((string) $value);
        }

        if ($field === 'inactive_status') {
            return match ($value) {
                'pending_document' => 'With document but still pending',
                'dissolved' => 'Inactive',
                null => 'Blank',
                default => Str::headline((string) $value),
            };
        }

        if ($value === null || $value === '') {
            return 'Blank';
        }

        return (string) $value;
    }

    private function recordRegionItemHistory(RegionItem $regionItem, string $action, string $updatedBy, array $changeSet): void
    {
        RegionItemHistory::query()->create([
            'region_item_id' => $regionItem->id,
            'region_id' => $regionItem->region_id,
            'region_name' => $regionItem->region?->name,
            'st_title' => $regionItem->title,
            'province' => $regionItem->province,
            'city' => $regionItem->municipality,
            'updated_by' => $updatedBy,
            'action' => $action,
            'update_row' => empty($changeSet)
                ? 'No tracked field changes.'
                : collect($changeSet)
                    ->map(fn (array $change) => $change['field'] . ': ' . $change['from'] . ' -> ' . $change['to'])
                    ->implode('; '),
        ]);
    }

    private function buildOverview(Collection $regions, Collection $regionItems): array
    {
        $statusCounts = [
            'Ongoing' => 0,
            'Inactive' => 0,
            'Unspecified' => 0,
        ];

        foreach ($regionItems as $item) {
            $status = Str::lower((string) $item->status);
            if ($status === 'ongoing') {
                $statusCounts['Ongoing']++;
            } elseif (in_array($status, ['inactive', 'dissolved'], true)) {
                $statusCounts['Inactive']++;
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