<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use PhpOffice\PhpSpreadsheet\IOFactory;
use Illuminate\Support\Facades\Cache;
use App\Models\StsAttachment;
use App\Models\User;
use App\Models\GalleryCard;
use App\Models\RegionItem;
use App\Services\RegionDashboardDataService;

class MainReportController extends Controller
{
    protected function getPrimaryDashboardData(?string $path = null): array
    {
        $service = app(RegionDashboardDataService::class);

        if ($service->hasData()) {
            return $service->getData();
        }

        if ($path && file_exists($path)) {
            return $this->getParsedData($path);
        }

        return [
            'regions' => [],
            'titles' => [],
            'provinces' => [],
            'municipalities' => [],
            'years' => [],
            'data' => [],
            'regionMap' => [],
            'headers' => [],
        ];
    }

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

    public function getParsedData(string $path): array
    {
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
                
                if (!isset($headers)) {
                    $headers = $normHeader;
                }
                
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
                    
                    if ($adoptIdx === false && stripos($h, 'adopt') !== false) {
                        $adoptIdx = $i;
                    }
                    
                    if ($repIdx === false && stripos($h, 'replic') !== false) {
                        if ($adoptIdx !== false && $i === $adoptIdx) {
                            
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
                
                    $clean = function($s) {
                        if (!is_string($s)) return '';
                        return preg_replace('/[\x00-\x1F\x7F]+/u','',$s);
                    };
                    $title = trim($clean($row[$titleIdx] ?? ''));
                    $province = trim($clean($row[$provinceIdx] ?? ''));
                    $municipality = trim($clean($row[$municipalityIdx] ?? ''));
                    
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
                            
                            'headers' => $normHeader,
                        ];
                    } elseif (empty($regionMap[$sheetName]['headers'])) {
                        
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
        
        $embedRaw = $request->query('embed', null);
        $embed = false;
        if ($embedRaw !== null) {
            $embedStr = strtolower(trim((string)$embedRaw));
            $embed = in_array($embedStr, ['1', 'true', 'yes', 'on'], true);
        }
        $path = $this->findLatestExcelPath();
        $parsed = $this->getPrimaryDashboardData($path);

        try {
            $regionItems = RegionItem::with('region:id,name')
                ->get(['id', 'region_id', 'title', 'province', 'municipality', 'year_of_resolution', 'with_res']);

            $dbMap = [];
            foreach ($regionItems as $ri) {
                $rname = $ri->region?->name ?? '';
                $key = implode('|', [
                    strtolower(trim((string) $rname)),
                    strtolower(trim((string) $ri->title)),
                    strtolower(trim((string) ($ri->province ?? ''))),
                    strtolower(trim((string) ($ri->municipality ?? ''))),
                ]);
                $dbMap[$key] = $ri;
            }

            if (!empty($parsed['data'])) {
                foreach ($parsed['data'] as $i => $row) {
                    $key = implode('|', [
                        strtolower(trim((string) ($row['region'] ?? ''))),
                        strtolower(trim((string) ($row['title'] ?? ''))),
                        strtolower(trim((string) ($row['province'] ?? ''))),
                        strtolower(trim((string) ($row['municipality'] ?? ''))),
                    ]);
                    if (isset($dbMap[$key])) {
                        $parsed['data'][$i]['year_of_resolution'] = $dbMap[$key]->year_of_resolution;
                        $parsed['data'][$i]['with_res'] = $dbMap[$key]->with_res ?? ($parsed['data'][$i]['with_res'] ?? null);
                    }
                }
            }
        } catch (\Throwable $e) {
        }

        if (empty($parsed['data'])) {
            $galleryCards = GalleryCard::with([
                'children' => function($q){ $q->whereNull('parent_child_id')->orderBy('docno','asc'); },
                'children.children' => function($q){ $q->orderBy('docno','asc'); }
            ])->where('is_active', 1)->orderBy('docno','asc')->get();

            
            return view('dashboard.mainreports.STsReport', [
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

        $regions = $parsed['regions'];
        $titles = $parsed['titles'];
        $provinces = $parsed['provinces'];
        $municipalities = $parsed['municipalities'];
        $years = $parsed['years'];
        $data = $parsed['data'];
        $regionMap = $parsed['regionMap'];

        

        
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

        
        $regionFilteredData = $data;
        
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

        
        $availableYearsMap = [];
        foreach ($regionFilteredData as $row) {
            if (!empty($row['year_of_moa'])) {
                $availableYearsMap[$row['year_of_moa']] = true;
            }
        }

        
        $allYears = array_keys($years);
        $availableYears = !empty($availableYearsMap) ? array_keys($availableYearsMap) : $allYears;

        
        $filteredData = $regionFilteredData;
        if (!empty($selectedYears)) {
            $filteredData = array_filter($filteredData, function($row) use ($selectedYears) {
                return isset($row['year_of_moa']) && in_array($row['year_of_moa'], $selectedYears);
            });
        }

        
        $filteredData = $this->addAttachmentInfo(array_values($filteredData));

        
        $yearStats = [];
        $totalExpr = 0;
        $totalRes = 0;
        $totalMoa = 0;
        $totalAdopted = 0;
        $totalReplicated = 0;
        
        $totalOngoingStatus = 0;
        $totalDissolvedStatus = 0;
        $headersArr = $parsed['headers'] ?? [];
        
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
                if (strpos($st,'ongoing') !== false || $st === 'on going') {
                    $ongoingCount = 1;
                } elseif (strpos($st,'dissolved') !== false || strpos($st,'inactive') !== false || strpos($st,'completed') !== false) {
                    $dissolvedCount = 1;
                }
            }

            
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

        return view('dashboard.mainreports.STsReport', [
            'regions' => $regions,
            'titles' => array_keys($titles),
            'provinces' => array_keys($provinces),
            'municipalities' => array_keys($municipalities),
            'years' => $availableYears,
            'allYears' => $allYears,
            'regionMap' => $regionMap,
            'data' => $filteredData,  
            'regionFilteredData' => $regionFilteredData,
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

        
    public function titleListingAjax(Request $request)
    {
        $path = $this->findLatestExcelPath();
        $parsed = $this->getPrimaryDashboardData($path);

        if (empty($parsed['data'])) {
            return response()->view('dashboard.mainreports.partials.title_listing_ajax', ['data' => []]);
        }
        $allData = $parsed['data'] ?? [];

        $regionFilteredData = $allData;
        $selectedRegions = $request->input('region', []);
        $selectedProvinces = $request->input('province', []);
        $selectedMunicipalities = $request->input('municipality', []);
        $selectedYears = (array) $request->input('year_of_moa', []);
        if ($request->query('embed')) {
            
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

        
        return response()->view('dashboard.mainreports.partials.title_listing_ajax', [
            'data' => $filteredData,
        ]);
    }

    
    public function prewarm(Request $request)
    {
        $path = $this->findLatestExcelPath();
        $service = app(RegionDashboardDataService::class);
        if ($service->hasData()) {
            $service->getData();
        } elseif ($path) {
            $this->getParsedData($path);
        }

        return response()->json(['status' => 'ok']);
    }
}
