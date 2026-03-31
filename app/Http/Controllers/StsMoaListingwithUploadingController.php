<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\Cache;
use App\Models\StsAttachment;
use App\Models\User;
use PhpOffice\PhpSpreadsheet\IOFactory;

class StsMoaListingwithUploadingController extends Controller
{
    public function index(Request $request)
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

        $regions = [];
        $rows = [];

        if ($path && file_exists($path)) {
            $cacheKey = 'sts_moa_listing_rows_' . md5($path . '|' . filemtime($path));

            $cached = Cache::store('file')->rememberForever($cacheKey, function () use ($path) {
                $reader = IOFactory::createReaderForFile($path);
                $reader->setReadDataOnly(true);
                $reader->setReadEmptyCells(false);
                $spreadsheet = $reader->load($path);
                $sheetNames = $spreadsheet->getSheetNames();
                $regionsLocal = array_filter($sheetNames, function ($name) {
                    return stripos($name, 'Data CY 2020-2022') === false;
                });

                $rowsLocal = [];
                foreach ($sheetNames as $sheetName) {
                    if (stripos($sheetName, 'Data CY 2020-2022') !== false) {
                        continue;
                    }
                    $sheet = $spreadsheet->getSheetByName($sheetName);
                    $rowsArray = $sheet->toArray(null, false, false, false);
                    if (count($rowsArray) < 2) {
                        continue;
                    }
                    $header = $rowsArray[0];
                    $normHeader = array_map(function ($h) {
                        return strtolower(trim($h));
                    }, $header);

                    $titleIdx = array_search('title of st', $normHeader);
                    $provinceIdx = array_search('province', $normHeader);
                    $municipalityIdx = array_search('name of municipality', $normHeader);
                    $yearIdx = array_search('year of moa', $normHeader);

                    $withMoaIdx = null;
                    foreach ($normHeader as $idx => $name) {
                        if ($name === '') {
                            continue;
                        }
                        if (strpos($name, 'with moa') !== false || $name === 'moa' || $name === 'with_moa') {
                            $withMoaIdx = $idx;
                            break;
                        }
                    }

                    if ($titleIdx === false || $provinceIdx === false || $municipalityIdx === false) {
                        continue;
                    }

                    foreach (array_slice($rowsArray, 1) as $row) {
                        $title = trim($row[$titleIdx] ?? '');
                        if ($title === '') {
                            continue;
                        }

                        $year = $yearIdx !== false ? trim($row[$yearIdx] ?? '') : '';

                        if ($year === '') {
                            continue;
                        }

                        if ($withMoaIdx !== null) {
                            $raw = $row[$withMoaIdx] ?? null;
                            $val = is_bool($raw) ? $raw : strtolower(trim((string) $raw));
                            $truthy = false;
                            if (is_bool($val)) {
                                $truthy = $val;
                            } else {
                                $truthy = in_array($val, ['1', 'true', 'yes', 'y', '✔', '✓'], true);
                            }
                            if (!$truthy) {
                                continue;
                            }
                        }

                        $rowsLocal[] = [
                            'region' => $sheetName,
                            'title' => $title,
                            'province' => trim($row[$provinceIdx] ?? ''),
                            'municipality' => trim($row[$municipalityIdx] ?? ''),
                            'year_of_moa' => $year,
                        ];
                    }
                }

                return [
                    'regions' => array_values($regionsLocal),
                    'rows' => $rowsLocal,
                ];
            });

            $regions = $cached['regions'] ?? [];
            $rows = $cached['rows'] ?? [];
        }

        $allTitles = [];
        $regionTitleMap = [];

        $provinceOptions = [];
        $cityOptions = [];

        foreach ($rows as $entry) {
            $title = $entry['title'] ?? '';
            $region = $entry['region'] ?? '';
            $province = trim($entry['province'] ?? '');
            $city = trim($entry['municipality'] ?? '');

            if ($title !== '' && $region !== '') {
                $allTitles[$title] = true;
                if (!isset($regionTitleMap[$region])) {
                    $regionTitleMap[$region] = [];
                }
                $regionTitleMap[$region][$title] = true;
            }

        }

        $titles = array_keys($allTitles);
        sort($titles, SORT_NATURAL | SORT_FLAG_CASE);
        foreach ($regionTitleMap as $region => $titleSet) {
            $list = array_keys($titleSet);
            sort($list, SORT_NATURAL | SORT_FLAG_CASE);
            $regionTitleMap[$region] = $list;
        }

        $selectedRegion = $request->input('region');
        $selectedProvince = trim((string) $request->input('province', ''));
        $selectedCity = trim((string) $request->input('city', ''));

        foreach ($rows as $entry) {
            $region = $entry['region'] ?? '';
            $province = trim($entry['province'] ?? '');
            $city = trim($entry['municipality'] ?? '');

            if ($selectedRegion && $region !== $selectedRegion) {
                continue;
            }

            if ($province !== '') {
                $provinceOptions[$province] = true;
            }

            if ($selectedProvince && strcasecmp($province, $selectedProvince) !== 0) {
                continue;
            }

            if ($city !== '') {
                $cityOptions[$city] = true;
            }
        }

        $provinceOptions = array_keys($provinceOptions);
        sort($provinceOptions, SORT_NATURAL | SORT_FLAG_CASE);

        $cityOptions = array_keys($cityOptions);
        sort($cityOptions, SORT_NATURAL | SORT_FLAG_CASE);

        $filtered = array_values(array_filter($rows, function ($row) use ($selectedRegion, $selectedProvince, $selectedCity) {
            if ($selectedRegion && ($row['region'] ?? '') !== $selectedRegion) {
                return false;
            }
            if ($selectedProvince && strcasecmp(trim($row['province'] ?? ''), $selectedProvince) !== 0) {
                return false;
            }
            if ($selectedCity && strcasecmp(trim($row['municipality'] ?? ''), $selectedCity) !== 0) {
                return false;
            }
            return true;
        }));

        $searchTitle = trim((string) $request->input('title', ''));
        if ($searchTitle !== '') {
            $needle = strtolower($searchTitle);
            $filtered = array_values(array_filter($filtered, function ($row) use ($needle) {
                if (!isset($row['title'])) {
                    return false;
                }
                $haystack = strtolower((string) $row['title']);
                return $haystack !== '' && strpos($haystack, $needle) !== false;
            }));
        }

        $perPage = 10;
        $currentPage = max(1, (int) $request->input('page', 1));
        $total = count($filtered);
        $offset = ($currentPage - 1) * $perPage;
        $itemsForPage = array_slice($filtered, $offset, $perPage);

        if (!empty($itemsForPage)) {
            $attachmentsQuery = StsAttachment::query();

            $attachmentsQuery->where(function ($q) use ($itemsForPage) {
                foreach ($itemsForPage as $row) {
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

            foreach ($itemsForPage as &$row) {
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
                    $row['attachment_url'] = $attachmentMap[$mapKey]['url'];
                    $row['attachment_id'] = $attachmentMap[$mapKey]['id'];
                    $row['attachment_uploaded_by'] = $attachmentMap[$mapKey]['uploaded_by'];
                }
            }
            unset($row);
        }

        $sts = new LengthAwarePaginator(
            $itemsForPage,
            $total,
            $perPage,
            $currentPage,
            [
                'path' => $request->url(),
                'query' => $request->query(),
            ]
        );

        if ($request->ajax()) {
            $html = view('dashboard.maincomponents.partials.uploadingstattachment_list', [
                'sts' => $sts,
                'selectedRegion' => $selectedRegion,
                'selectedProvince' => $selectedProvince,
                'selectedCity' => $selectedCity,
                'searchTitle' => $searchTitle,
            ])->render();

            return response()->json(['html' => $html]);
        }

        return view('dashboard.maincomponents.uploadingstattachment', [
            'regions' => $regions,
            'selectedRegion' => $selectedRegion,
            'selectedProvince' => $selectedProvince,
            'selectedCity' => $selectedCity,
            'searchTitle' => $searchTitle,
            'titles' => $titles,
            'regionTitleMap' => $regionTitleMap,
            'provinceOptions' => $provinceOptions,
            'cityOptions' => $cityOptions,
            'sts' => $sts,
        ]);
    }
}
