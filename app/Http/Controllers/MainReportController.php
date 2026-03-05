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
        // bump version when logic changes so old cache is ignored
        $cacheKey = 'stsreport_parsed_v6_' . md5($path . '|' . filemtime($path));
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
                // locate header row within the first few rows (skip blanks/prefixes)
                $headerRowIdx = null;
                foreach (range(0, min(4, count($rows) - 1)) as $i) {
                    $trial = array_map(function ($h) {
                        return strtolower(trim((string)$h));
                    }, $rows[$i]);
                    if (
                        array_search('title of st', $trial) !== false &&
                        array_search('province', $trial) !== false &&
                        array_search('name of municipality', $trial) !== false
                    ) {
                        $headerRowIdx = $i;
                        break;
                    }
                }
                if ($headerRowIdx === null) {
                    continue;
                }
                $header = $rows[$headerRowIdx];
                $normHeader = array_map(function ($h) {
                    return strtolower(trim((string)$h));
                }, $header);
                // combine with next row if present for multi-line headings
                if (isset($rows[$headerRowIdx + 1])) {
                    $header2 = $rows[$headerRowIdx + 1];
                    $normHeader2 = array_map(function ($h) {
                        return strtolower(trim((string)$h));
                    }, $header2);
                    $combined = [];
                    $max = max(count($normHeader), count($normHeader2));
                    for ($i = 0; $i < $max; $i++) {
                        $h1 = $normHeader[$i] ?? '';
                        $h2 = $normHeader2[$i] ?? '';
                        if ($h1 && $h2) {
                            $combined[] = trim($h1 . ' ' . $h2);
                        } else {
                            $combined[] = $h1 . $h2;
                        }
                    }
                    $normHeader = $combined;
                }
                // keep headers from the first sheet we parse (global fallback)
                if (!isset($headers)) {
                    $headers = $normHeader;
                }
                // locate important column indexes using substring matching to handle variations in header labels; we want to be flexible to
                $titleIdx = $provinceIdx = $municipalityIdx = $exprIdx = $moaIdx = $resIdx = $yearIdx = $adoptIdx = $repIdx = false;
                foreach ($normHeader as $i => $h) {
                    if ($titleIdx === false && stripos($h, 'title') !== false) {
                        $titleIdx = $i;
                    }
                    if ($provinceIdx === false && stripos($h, 'province') !== false) {
                        $provinceIdx = $i;
                    }
                    if ($municipalityIdx === false && stripos($h, 'municipality') !== false) {
                        $municipalityIdx = $i;
                    }
                    if ($exprIdx === false && (
                        stripos($h, 'expression') !== false ||
                        stripos($h, 'expr') !== false ||
                        stripos($h, 'interest') !== false
                    )) {
                        $exprIdx = $i;
                    }
                    if ($moaIdx === false && stripos($h, 'moa') !== false) {
                        $moaIdx = $i;
                    }
                    if ($resIdx === false && (
                        stripos($h, 'resolution') !== false ||
                        stripos($h, 'res ') !== false ||
                        stripos($h, 'sb') !== false
                    )) {
                        $resIdx = $i;
                    }
                    if ($yearIdx === false && stripos($h, 'year') !== false) {
                        $yearIdx = $i;
                    }
                    // pick adoption column: first header cell that mentions "adopt"
                    // (some workbooks use combined labels like "Adopted/Replicated").
                    if ($adoptIdx === false && stripos($h, 'adopt') !== false) {
                        $adoptIdx = $i;
                    }
                    // pick replicate column: prefer a dedicated "Replicated" column
                    // that is different from the Adopted column. This prevents
                    // both Adopted and Replicated from pointing at the same
                    // "Adopted/Replicated" header when a separate Replicated
                    // column is present (as in FO X).
                    if ($repIdx === false && stripos($h, 'replic') !== false) {
                        if ($adoptIdx !== false && $i === $adoptIdx) {
                            // skip this index so we can pick a later column
                            // that is dedicated to Replicated only
                        } else {
                            $repIdx = $i;
                        }
                    }
                }
                if ($titleIdx === false || $provinceIdx === false || $municipalityIdx === false) {
                    continue;
                }
                $startSlice = $headerRowIdx + 1;
                foreach (array_slice($rows, $startSlice) as $row) {
                    // remove non-printable/control characters (vertical tab, ETX,
                    // etc) before we trim.  Excel exports sometimes contain those
                    // which break our front‑end matching and make cards appear
                    // empty even though the count badge is correct.
                    $clean = function($s) {
                        if (!is_string($s)) return '';
                        // strip ASCII control characters (0x00-0x1F,0x7F)
                        return preg_replace('/[\x00-\x1F\x7F]+/u','',$s);
                    };
                    $title = trim($clean($row[$titleIdx] ?? ''));
                    $province = trim($clean($row[$provinceIdx] ?? ''));
                    $municipality = trim($clean($row[$municipalityIdx] ?? ''));
                    // skip rows that have no meaningful title after trimming; these
                    // would otherwise show up as blank/"(no title)" entries in the
                    // UI and confuse users.  downstream filtering and JS also
                    // remove blanks, but pre-filtering here avoids keeping them in
                    // the master dataset at all.
                    if ($title === '') {
                        continue;
                    }
                    $with_expr = ($exprIdx !== false && isset($row[$exprIdx])) ? $row[$exprIdx] : null;
                    $with_moa = ($moaIdx !== false && isset($row[$moaIdx])) ? $row[$moaIdx] : null;
                    $with_res = ($resIdx !== false && isset($row[$resIdx])) ? $row[$resIdx] : null;
                    $with_adopted = ($adoptIdx !== false && isset($row[$adoptIdx])) ? $row[$adoptIdx] : null;
                    $with_replicated = ($repIdx !== false && isset($row[$repIdx])) ? $row[$repIdx] : null;
                    $titles[$title] = true;
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
                        'with_adopted' => $with_adopted,
                        'with_replicated' => $with_replicated,
                        'year_of_moa' => $year_of_moa,
                        'row' => $row,
                    ];
                    if (!isset($regionMap[$sheetName])) {
                        $regionMap[$sheetName] = [
                            'provinces' => [],
                            'years' => [],
                            // store per-sheet headers so that downstream
                            // aggregation can resolve Ongoing/Dissolved
                            // column indexes for each region individually.
                            'headers' => $normHeader,
                        ];
                    } elseif (empty($regionMap[$sheetName]['headers'])) {
                        // in case the region entry was created earlier,
                        // ensure its headers are populated.
                        $regionMap[$sheetName]['headers'] = $normHeader;
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
                'headers' => $headers ?? [],
            ];
        });
    }

    public function index(Request $request)
    {
        // determine if the view is being embedded (e.g. via iframe)
        $embed = $request->query('embed');
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
                'embed' => $embed,
                'headers' => [],
                'yearStats' => [],
                'totalExpr' => 0,
                'totalRes' => 0,
                'totalMoa' => 0,
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

        // when the report is being shown inside the iframe the parent page
        // already manages filtering for the slider/gallery via postMessage; the
        // totals graphs and title listing should remain at the unfiltered global
        // level. detect the `embed` flag and clear any location/year selections
        // so the subsequent `$data` arrays are left untouched.
        $selectedRegions = $request->input('region', []);
        $selectedProvinces = $request->input('province', []);
        $selectedMunicipalities = $request->input('municipality', []);
        $selectedYears = (array) $request->input('year_of_moa', []);
        if ($embed) {
            $selectedRegions = [];
            $selectedProvinces = [];
            $selectedMunicipalities = [];
            $selectedYears = [];
        }

        // First, filter by region/province/municipality only (for bar chart)
        $regionFilteredData = $data;
        // propagate embed flag to view data
        $viewData['embed'] = $embed;

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

        // compute yearStats and high-level totals for use in charts
        $yearStats = [];
        $totalExpr = 0;
        $totalRes = 0;
        $totalMoa = 0;
        $totalAdopted = 0;
        $totalReplicated = 0;
        // explicit counts based only on the dedicated
        // Ongoing / Dissolved columns from the upload
        $totalOngoingStatus = 0;
        $totalDissolvedStatus = 0;
        $headersArr = $parsed['headers'] ?? [];
        // pre-compute status column indexes per region so that regions whose
        // sheets have extra/missing columns (like Region X) still map to the
        // correct Ongoing / Dissolved cells.
        $statusIndexByRegion = [];
        foreach ($regionMap as $regionName => $meta) {
            $hdrs = $meta['headers'] ?? $headersArr;
            $locIdxO = null;
            $locIdxD = null;
            foreach ($hdrs as $i => $h) {
                if ($locIdxO === null && stripos($h, 'ongoing') !== false) {
                    $locIdxO = $i;
                }
                if ($locIdxD === null && (stripos($h, 'dissolved') !== false || stripos($h, 'inactive') !== false)) {
                    $locIdxD = $i;
                }
            }
            $statusIndexByRegion[$regionName] = [
                'ongoing' => $locIdxO,
                'dissolved' => $locIdxD,
            ];
        }
        $normalizeBool = function($v) {
            if (is_bool($v)) return $v;
            $s = strtolower(trim((string)$v));
            return $s === 'true' || $s === '1';
        };
        // Determine whether a status cell should be treated as TRUE.
        // Per your request we now count *only* explicit TRUE values
        // coming from the sheet (boolean true or the string "TRUE").
        // Other markers like "YES", "X", or numeric counts are ignored
        // for the Ongoing/Dissolved cards.
        $statusCellIsTrue = function($v) {
            if (is_bool($v)) {
                return $v;
            }
            if ($v === null) {
                return false;
            }
            $s = strtolower(trim((string) $v));
            return $s === 'true';
        };
        // adopted/replicated columns may contain either boolean-like values or
        // numeric counts.  When a number is provided we sum it, otherwise we
        // treat any truthy/checked value as a single instance.
        $normalizeCount = function($v) use ($normalizeBool) {
            if (is_numeric($v)) {
                return (int) $v;
            }
            if ($normalizeBool($v)) {
                return 1;
            }
            $s = trim((string)$v);
            return $s !== '' ? 1 : 0;
        };
        // use the fully filtered dataset (respecting region/year filters)
        // so that dashboard totals and charts reflect the user's selection
        foreach ($filteredData as $r) {
            $regionName = $r['region'] ?? null;
            $idxOng = $statusIndexByRegion[$regionName]['ongoing'] ?? null;
            $idxDis = $statusIndexByRegion[$regionName]['dissolved'] ?? null;
            $yr = $r['year_of_moa'] ?: 'Unknown';
            if (!isset($yearStats[$yr])) {
                $yearStats[$yr] = ['total'=>0,'ongoing'=>0,'dissolved'=>0];
            }
            $yearStats[$yr]['total']++;
            if ($normalizeBool($r['with_expr'] ?? false)) $totalExpr++;
            if ($normalizeBool($r['with_res'] ?? false)) $totalRes++;
            if ($normalizeBool($r['with_moa'] ?? false)) $totalMoa++;
            $totalAdopted += $normalizeCount($r['with_adopted'] ?? null);
            $totalReplicated += $normalizeCount($r['with_replicated'] ?? null);
            $st = '';
            if (!empty($r['status'])) {
                $st = strtolower($r['status']);
            }

            // For ongoing/dissolved totals we now rely primarily on the
            // dedicated Ongoing / Dissolved columns.  Each TRUE/marked cell
            // in those columns counts as 1; if the workbook supplies a
            // numeric value we treat it as that many STs.  Only when a
            // region sheet does not expose those columns at all do we fall
            // back to the free‑text status field.

            $ongoingCount = 0;
            $dissolvedCount = 0;

            if ($idxOng !== null || $idxDis !== null) {
                if ($idxOng !== null && isset($r['row'][$idxOng])) {
                    $val = $r['row'][$idxOng];
                    if (is_numeric($val)) {
                        $ongoingCount = max(0, (int) $val);
                    } elseif ($statusCellIsTrue($val)) {
                        $ongoingCount = 1;
                    }
                }

                if ($idxDis !== null && isset($r['row'][$idxDis])) {
                    $val = $r['row'][$idxDis];
                    if (is_numeric($val)) {
                        $dissolvedCount = max(0, (int) $val);
                    } elseif ($statusCellIsTrue($val)) {
                        $dissolvedCount = 1;
                    }
                }
            } else {
                // no dedicated columns found for this region; use status text
                if (strpos($st,'ongoing') !== false || $st === 'on going') {
                    $ongoingCount = 1;
                } elseif (strpos($st,'dissolved') !== false || strpos($st,'inactive') !== false || strpos($st,'completed') !== false) {
                    $dissolvedCount = 1;
                }
            }

            // final status-based tallies per year using the derived counts
            if ($ongoingCount > 0) {
                $yearStats[$yr]['ongoing'] += $ongoingCount;
                $totalOngoingStatus += $ongoingCount;
            }
            if ($dissolvedCount > 0) {
                $yearStats[$yr]['dissolved'] += $dissolvedCount;
                $totalDissolvedStatus += $dissolvedCount;
            }
        }

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
            'embed' => $embed,
            'headers' => $parsed['headers'] ?? [],
            'yearStats' => $yearStats,
            'totalExpr' => $totalExpr,
            'totalRes' => $totalRes,
            'totalMoa' => $totalMoa,
            'totalAdopted' => $totalAdopted,
            'totalReplicated' => $totalReplicated,
            'totalOngoingStatus' => $totalOngoingStatus,
            'totalDissolvedStatus' => $totalDissolvedStatus,
        ]);    }

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
        if ($request->query('embed')) {
            // ignore outer filters when embedded so the listing always shows
            // the full dataset; parent page already applies visual hints via
            // postMessage if needed.
            $selectedRegions = [];
            $selectedProvinces = [];
            $selectedMunicipalities = [];
            $selectedYears = [];
        }

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
