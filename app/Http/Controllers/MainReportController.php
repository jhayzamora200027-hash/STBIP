<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use PhpOffice\PhpSpreadsheet\IOFactory;
use Illuminate\Support\Facades\Cache;
use App\Models\StsAttachment;
use App\Models\User;
use App\Models\GalleryCard;

class MainReportController extends Controller
{
    /**
     * Attach latest attachment info (if any) to each ST row.
     */
    protected function addAttachmentInfo(array $rows): array
    {
        if (empty($rows)) {
            return $rows;
        }

        $attachmentsQuery = StsAttachment::query();

        $attachmentsQuery->where(function ($q) use ($rows) {
            foreach ($rows as $row) {
                $q->orWhere(function ($sub) use ($row) {
                    $sub->where('region', $row['region'] ?? null)
                        ->where('province', $row['province'] ?? null)
                        ->where('municipality', $row['municipality'] ?? null)
                        ->where('title', $row['title'] ?? null)
                        ->where('year_of_moa', $row['year_of_moa'] ?? null);
                });
            }
        });

        $attachments = $attachmentsQuery->get();

        // Resolve created_by (user_id) to user name once per unique id
        $userNames = [];
        $userIds = $attachments->pluck('created_by')->filter()->unique()->values();
        if ($userIds->isNotEmpty()) {
            $userNames = User::whereIn('user_id', $userIds)->pluck('name', 'user_id')->toArray();
        }

        $attachmentMap = [];
        foreach ($attachments as $attachment) {
            $key = implode('|', [
                $attachment->region,
                $attachment->province,
                $attachment->municipality,
                $attachment->title,
                $attachment->year_of_moa,
            ]);

            if (!isset($attachmentMap[$key]) || $attachment->id > $attachmentMap[$key]['id']) {
                $entry = [
                    'id' => $attachment->id,
                    'action' => $attachment->action,
                    'url' => null,
                    // Display name for Title Listing/map: prefer User.name, fall back to raw user_id
                    'uploaded_by' => $userNames[$attachment->created_by] ?? $attachment->created_by,
                ];

                if ($attachment->action === 'added') {
                    $entry['url'] = route('sts.attachments.show', $attachment->id);
                }

                $attachmentMap[$key] = $entry;
            }
        }

        foreach ($rows as &$row) {
            $mapKey = implode('|', [
                $row['region'] ?? null,
                $row['province'] ?? null,
                $row['municipality'] ?? null,
                $row['title'] ?? null,
                $row['year_of_moa'] ?? null,
            ]);

            if (isset($attachmentMap[$mapKey]) &&
                $attachmentMap[$mapKey]['action'] === 'added' &&
                !empty($attachmentMap[$mapKey]['url'])
            ) {
                $row['attachment_id'] = $attachmentMap[$mapKey]['id'];
                $row['attachment_url'] = $attachmentMap[$mapKey]['url'];
                $row['attachment_uploaded_by'] = $attachmentMap[$mapKey]['uploaded_by'];
            }
        }
        unset($row);

        return $rows;
    }

    /**
     * Locate the most recent or selected Excel path.
     */
    public function findLatestExcelPath(): ?string
    {
        $excelDir = storage_path('app/excels');
        $baseExcel = null;
        $selectLog = \App\Models\Selectdocslogs::orderBy('updated_at', 'desc')->get();
        foreach ($selectLog as $log) {
            if ($log->excelname && trim($log->excelname) !== '') {
                $full = $excelDir . '/' . $log->excelname;
                if (file_exists($full) && is_readable($full) && is_file($full)) {
                    $baseExcel = $log->excelname;
                    break;
                }
            }
        }

        $path = null;
        if ($baseExcel) {
            $path = $excelDir . '/' . $baseExcel;
        }
        if (!$path || !file_exists($path)) {
            $xlsxFiles = glob($excelDir . '/*.xlsx') ?: [];
            $xlsFiles = glob($excelDir . '/*.xls') ?: [];
            $files = array_merge($xlsxFiles, $xlsFiles);
            if ($files && count($files) > 0) {
                usort($files, function ($a, $b) {
                    return filemtime($b) - filemtime($a);
                });
                $path = $files[0];
            }
        }

        return $path && file_exists($path) ? $path : null;
    }

    /**
     * Parse Excel and cache result (shared by multiple endpoints).
     */
    public function getParsedData(string $path): array
    {
        // Cache heavy Excel parsing so STsReport loads faster.
        // Use file cache store for large payloads to avoid DB overhead.
        $cacheKey = 'stsreport_parsed_' . md5($path . '|' . filemtime($path));
        $cacheStore = Cache::store('file');

        return $cacheStore->rememberForever($cacheKey, function () use ($path) {
            $reader = IOFactory::createReaderForFile($path);
            $reader->setReadDataOnly(true);
            $reader->setReadEmptyCells(false);
            $spreadsheet = $reader->load($path);
            $sheetNames = $spreadsheet->getSheetNames();
            $regions = $sheetNames;
            $titles = [];
            $provinces = [];
            $municipalities = [];
            $years = [];
            $data = [];
            $regionMap = [];

            foreach ($sheetNames as $sheetName) {
                if (stripos($sheetName, 'Data CY 2020-2022') !== false) {
                    continue;
                }
                $sheet = $spreadsheet->getSheetByName($sheetName);
                $rows = $sheet->toArray(null, false, false, false);
                if (count($rows) < 2) {
                    continue;
                }
                $header = $rows[0];
                $normHeader = array_map(function ($h) {
                    return strtolower(trim($h));
                }, $header);
                $titleIdx = array_search('title of st', $normHeader);
                $provinceIdx = array_search('province', $normHeader);
                $municipalityIdx = array_search('name of municipality', $normHeader);
                $exprIdx = array_search('with expression of interest', $normHeader);
                $moaIdx = array_search('with moa', $normHeader);
                $resIdx = array_search('with resolution', $normHeader);
                $yearIdx = array_search('year of moa', $normHeader);
                if ($titleIdx === false || $provinceIdx === false || $municipalityIdx === false) {
                    continue;
                }
                foreach (array_slice($rows, 1) as $row) {
                    $title = trim($row[$titleIdx] ?? '');
                    $province = trim($row[$provinceIdx] ?? '');
                    $municipality = trim($row[$municipalityIdx] ?? '');
                    $with_expr = ($exprIdx !== false && isset($row[$exprIdx])) ? $row[$exprIdx] : null;
                    $with_moa = ($moaIdx !== false && isset($row[$moaIdx])) ? $row[$moaIdx] : null;
                    $with_res = ($resIdx !== false && isset($row[$resIdx])) ? $row[$resIdx] : null;
                    if ($title !== '') {
                        $titles[$title] = true;
                    }
                    if ($province !== '') {
                        $provinces[$province] = true;
                    }
                    if ($municipality !== '') {
                        $municipalities[$municipality] = true;
                    }
                    $year_of_moa = ($yearIdx !== false && isset($row[$yearIdx])) ? trim($row[$yearIdx]) : null;
                    if ($year_of_moa !== null && $year_of_moa !== '') {
                        $years[$year_of_moa] = true;
                    }
                    $data[] = [
                        'region' => $sheetName,
                        'title' => $title,
                        'province' => $province,
                        'municipality' => $municipality,
                        'with_expr' => $with_expr,
                        'with_moa' => $with_moa,
                        'with_res' => $with_res,
                        'year_of_moa' => $year_of_moa,
                        'row' => $row,
                    ];
                    if (!isset($regionMap[$sheetName])) {
                        $regionMap[$sheetName] = [
                            'provinces' => [],
                            'years' => [],
                        ];
                    }
                    if ($province !== '' && $municipality !== '') {
                        if (!isset($regionMap[$sheetName]['provinces'][$province])) {
                            $regionMap[$sheetName]['provinces'][$province] = [];
                        }
                        if (!in_array($municipality, $regionMap[$sheetName]['provinces'][$province])) {
                            $regionMap[$sheetName]['provinces'][$province][] = $municipality;
                        }
                    }
                    if ($year_of_moa !== null && $year_of_moa !== '' && !in_array($year_of_moa, $regionMap[$sheetName]['years'])) {
                        $regionMap[$sheetName]['years'][] = $year_of_moa;
                    }
                }
            }

            return [
                'regions' => $regions,
                'titles' => $titles,
                'provinces' => $provinces,
                'municipalities' => $municipalities,
                'years' => $years,
                'data' => $data,
                'regionMap' => $regionMap,
            ];
        });
    }

    public function index(Request $request)
    {
        $path = $this->findLatestExcelPath();
        if (!$path) {
            $galleryCards = GalleryCard::with([
                'children' => function($q){ $q->whereNull('parent_child_id')->orderBy('docno','asc'); },
                'children.children' => function($q){ $q->orderBy('docno','asc'); }
            ])->where('is_active', 1)->orderBy('docno','asc')->get();

            // Provide the same view variables (with safe defaults) so other views that
            // reuse this controller's View data (e.g. `dashboard.main`) never see
            // undefined variables like `$years` when no Excel is available.
            return view('dashboard.mainreports.STsreport', [
                'regions' => [],
                'titles' => [],
                'provinces' => [],
                'municipalities' => [],
                'years' => [],
                'allYears' => [],
                'regionMap' => [],
                'data' => [],
                'regionFilteredData' => [],
                'galleryCards' => $galleryCards,
            ]);
        }

        $parsed = $this->getParsedData($path);

        $regions = $parsed['regions'];
        $titles = $parsed['titles'];
        $provinces = $parsed['provinces'];
        $municipalities = $parsed['municipalities'];
        $years = $parsed['years'];
        $data = $parsed['data'];
        $regionMap = $parsed['regionMap'];

        // Apply filters from request


        // First, filter by region/province/municipality only (for bar chart)
        $regionFilteredData = $data;
        $selectedRegions = $request->input('region', []);
        $selectedProvinces = $request->input('province', []);
        $selectedMunicipalities = $request->input('municipality', []);
        $selectedYears = (array) $request->input('year_of_moa', []);

        if (!empty($selectedRegions)) {
            $regionFilteredData = array_filter($regionFilteredData, function($row) use ($selectedRegions) {
                return in_array($row['region'], $selectedRegions);
            });
        }
        if (!empty($selectedProvinces)) {
            $regionFilteredData = array_filter($regionFilteredData, function($row) use ($selectedProvinces) {
                return in_array($row['province'], $selectedProvinces);
            });
        }
        if (!empty($selectedMunicipalities)) {
            $regionFilteredData = array_filter($regionFilteredData, function($row) use ($selectedMunicipalities) {
                return in_array($row['municipality'], $selectedMunicipalities);
            });
        }

        // Dynamically determine available years based on the filtered
        // region/province/municipality data so the Year dropdown only
        // shows years that actually exist for the selected locations.
        $availableYearsMap = [];
        foreach ($regionFilteredData as $row) {
            if (!empty($row['year_of_moa'])) {
                $availableYearsMap[$row['year_of_moa']] = true;
            }
        }

        // If no region-level filters are applied (or no years were
        // found in the filtered set), fall back to all years found
        // in the workbook so the initial load still shows everything.
        $allYears = array_keys($years);
        $availableYears = !empty($availableYearsMap) ? array_keys($availableYearsMap) : $allYears;

        // Now, filter by year as well (for all other charts/totals)
        $filteredData = $regionFilteredData;
        if (!empty($selectedYears)) {
            $filteredData = array_filter($filteredData, function($row) use ($selectedYears) {
                return isset($row['year_of_moa']) && in_array($row['year_of_moa'], $selectedYears);
            });
        }

        // Attach current attachment information (if any) to each filtered row
        $filteredData = $this->addAttachmentInfo(array_values($filteredData));

        $galleryCards = GalleryCard::with([
            'children' => function($q){ $q->whereNull('parent_child_id')->orderBy('docno','asc'); },
            'children.children' => function($q){ $q->orderBy('docno','asc'); }
        ])->where('is_active', 1)->orderBy('docno','asc')->get();

        return view('dashboard.mainreports.STsreport', [
            'regions' => $regions,
            'titles' => array_keys($titles),
            'provinces' => array_keys($provinces),
            'municipalities' => array_keys($municipalities),
            'years' => $availableYears,
            'allYears' => $allYears,
            'regionMap' => $regionMap,
            'data' => $filteredData, // fully filtered for all other charts/totals (with attachment info)
            'regionFilteredData' => $regionFilteredData, // only region/province/municipality filtered for bar chart
            'galleryCards' => $galleryCards,
        ]);
    }

    // AJAX handler for Title Listing pagination
    public function titleListingAjax(Request $request)
    {
        $path = $this->findLatestExcelPath();
        if (!$path) {
            return response()->view('dashboard.mainreports.partials.title_listing_ajax', ['data' => []]);
        }

        $parsed = $this->getParsedData($path);
        $allData = $parsed['data'] ?? [];

        // Apply filters from request (same as index)
        $regionFilteredData = $allData;
        $selectedRegions = $request->input('region', []);
        $selectedProvinces = $request->input('province', []);
        $selectedMunicipalities = $request->input('municipality', []);
        $selectedYears = (array) $request->input('year_of_moa', []);

        if (!empty($selectedRegions)) {
            $regionFilteredData = array_filter($regionFilteredData, function($row) use ($selectedRegions) {
                return in_array($row['region'], $selectedRegions);
            });
        }
        if (!empty($selectedProvinces)) {
            $regionFilteredData = array_filter($regionFilteredData, function($row) use ($selectedProvinces) {
                return in_array($row['province'], $selectedProvinces);
            });
        }
        if (!empty($selectedMunicipalities)) {
            $regionFilteredData = array_filter($regionFilteredData, function($row) use ($selectedMunicipalities) {
                return in_array($row['municipality'], $selectedMunicipalities);
            });
        }
        $filteredData = $regionFilteredData;
        if (!empty($selectedYears)) {
            $filteredData = array_filter($filteredData, function($row) use ($selectedYears) {
                return isset($row['year_of_moa']) && in_array($row['year_of_moa'], $selectedYears);
            });
        }

        $filteredData = $this->addAttachmentInfo(array_values($filteredData));

        // Return only the partial Blade for AJAX
        return response()->view('dashboard.mainreports.partials.title_listing_ajax', [
            'data' => $filteredData,
        ]);
    }

    /**
     * Lightweight endpoint to warm the STsReport cache in the background.
     */
    public function prewarm(Request $request)
    {
        $path = $this->findLatestExcelPath();
        if ($path) {
            // Trigger cache population; result is discarded here.
            $this->getParsedData($path);
        }

        return response()->json(['status' => 'ok']);
    }
}
