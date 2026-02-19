<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class STsReportController extends Controller
{
    protected function getAllData()
    {
        $stsReportController = new MainReportController();
        $path = $stsReportController->findLatestExcelPath();

        if (!$path) {
            return ['data' => [], 'regions' => []];
        }

        $parsed = $stsReportController->getParsedData($path);

        return [
            'data' => $parsed['data'] ?? [],
            'regions' => $parsed['regions'] ?? [],
        ];
    }

    public function index()
    {
        $all = $this->getAllData();

        return view('demo1', [
            'filteredData' => $all['data'], // show all initially
        ]);
    }

    // PHP version of region inference logic from STsReport Title Listing
    private function inferRegionCodeFromRow($row) {
        if (!$row) return null;
        $regionText = strtolower(trim($row['region'] ?? ''));
        $province = strtolower(trim($row['province'] ?? ''));
        // Named-region aliases
        if (strpos($regionText, 'national capital') !== false || strpos($regionText, ' ncr') !== false || strpos($regionText, 'ncr') === 0) return 'NCR';
        if (strpos($regionText, 'ilocos') !== false) return 'Region I';
        if (strpos($regionText, 'cagayan valley') !== false) return 'Region II';
        if (strpos($regionText, 'central luzon') !== false) return 'Region III';
        if (strpos($regionText, 'calabarzon') !== false) return 'Region IV-A';
        if (strpos($regionText, 'mimaropa') !== false) return 'Region IV-B';
        if (strpos($regionText, 'bicol') !== false) return 'Region V';
        if (strpos($regionText, 'western visayas') !== false) return 'Region VI';
        if (strpos($regionText, 'central visayas') !== false) return 'Region VII';
        if (strpos($regionText, 'eastern visayas') !== false) return 'Region VIII';
        if (strpos($regionText, 'zamboanga peninsula') !== false || strpos($regionText, 'zamboanga pen') !== false) return 'Region IX';
        if (strpos($regionText, 'northern mindanao') !== false) return 'Region X';
        if (strpos($regionText, 'davao region') !== false) return 'Region XI';
        if (strpos($regionText, 'soccsksargen') !== false) return 'Region XII';
        if (strpos($regionText, 'caraga') !== false) return 'CARAGA';
        if ((strpos($regionText, 'bangsamoro') !== false || preg_match('/\bbarmm\b/', $regionText))) return 'BARMM';
        if (!strpos($regionText, 'caraga') && ($regionText === 'car' || strpos($regionText, 'cordillera') !== false || preg_match('/\bcar\b/', $regionText))) return 'CAR';
        // Roman numeral fallback
        $romanPatterns = [
            'Region XII' => '/\bxii\b/',
            'Region XI' => '/\bxi\b/',
            'Region X' => '/\bx\b/',
            'Region IX' => '/\bix\b/',
            'Region VIII' => '/\bviii\b/',
            'Region VII' => '/\bvii\b/',
            'Region VI' => '/\bvi\b/',
            'Region V' => '/\bv\b/',
            'Region IV-B' => '/\biv[\s-]?b\b/',
            'Region IV-A' => '/\biv[\s-]?a\b/',
            'Region III' => '/\biii\b/',
            'Region II' => '/\bii\b/',
            'Region I' => '/\bi\b/'
        ];
        foreach ($romanPatterns as $code => $re) {
            if (preg_match($re, $regionText)) return $code;
        }
        return null;
    }

    public function ajaxRegionTitles(Request $request)
    {
        $all = $this->getAllData();
        $region = $request->input('region_image');

        $filteredData = $all['data'];

        if (!empty($region)) {
            $filteredData = array_filter($all['data'], function ($row) use ($region) {
                return $this->inferRegionCodeFromRow($row) === $region;
            });
        }

        return view('partials.st_listing_rows', [
            'filteredData' => $filteredData,
        ]);
    }

    /**
     * Return hierarchical JSON for modal dropdowns: provinces -> cities -> ST rows
     */
    public function ajaxRegionHierarchy(Request $request)
    {
        $all = $this->getAllData();
        $region = $request->input('region_image');

        $filtered = $all['data'];
        if (!empty($region)) {
            $filtered = array_filter($filtered, function ($row) use ($region) {
                return $this->inferRegionCodeFromRow($row) === $region;
            });
        }

        // Normalize and filter out empty titles
        $filtered = array_values(array_filter($filtered, function ($r) {
            return !empty($r['title']) && trim($r['title']) !== '';
        }));

        // Count uploaded attachments for the currently-open region (distinct STs)
        $uploadedCount = 0;
        try {
            if (!empty($region)) {
                // Build a fast lookup of filtered ST rows (keys must match how attachments are logged)
                $filteredKeys = [];
                foreach ($filtered as $r) {
                    $k = implode('|', [
                        $r['region'] ?? null,
                        $r['province'] ?? null,
                        $r['municipality'] ?? ($r['city'] ?? null),
                        $r['title'] ?? null,
                        $r['year_of_moa'] ?? null,
                    ]);
                    $filteredKeys[$k] = true;
                }

                // Query attachments that match ANY of the filtered rows (use OR groups)
                $attachmentsQuery = \App\Models\StsAttachment::query();
                $attachmentsQuery->where('action', 'added');
                $attachmentsQuery->where(function ($q) use ($filtered) {
                    foreach ($filtered as $row) {
                        $q->orWhere(function ($sub) use ($row) {
                            $sub->where('region', $row['region'] ?? null)
                                ->where('province', $row['province'] ?? null)
                                ->where('municipality', $row['municipality'] ?? ($row['city'] ?? null))
                                ->where('title', $row['title'] ?? null)
                                ->where('year_of_moa', $row['year_of_moa'] ?? null);
                        });
                    }
                });
                $attachments = $attachmentsQuery->get();

                // Count distinct matched attachments by the same composite key
                $attachmentKeys = [];
                foreach ($attachments as $att) {
                    $key = implode('|', [
                        $att->region,
                        $att->province,
                        $att->municipality,
                        $att->title,
                        $att->year_of_moa,
                    ]);
                    if (isset($filteredKeys[$key])) {
                        $attachmentKeys[$key] = true;
                    }
                }
                $uploadedCount = count($attachmentKeys);
            }
        } catch (\Throwable $ex) {
            // ignore; default to zero
            $uploadedCount = 0;
        }

        $grouped = [];
        $provinces = [];

        foreach ($filtered as $row) {
            $prov = trim($row['province'] ?? '') ?: 'UNKNOWN';
            $city = trim($row['municipality'] ?? $row['city'] ?? '') ?: 'UNKNOWN';

            $provinces[] = $prov;
            if (!isset($grouped[$prov])) $grouped[$prov] = [];
            if (!isset($grouped[$prov][$city])) $grouped[$prov][$city] = [];

            $grouped[$prov][$city][] = [
                'title' => $row['title'] ?? '',
                'province' => $prov,
                'municipality' => $city,
            ];
        }

        $provinces = array_values(array_unique($provinces));

        // Determine available MOA years inside the filtered set and compute per-year totals
        $yearsMap = [];
        foreach ($filtered as $r) {
            $y = isset($r['year_of_moa']) ? trim((string)($r['year_of_moa'] ?? '')) : '';
            if ($y !== '') $yearsMap[$y] = true;
        }
        $availableYears = array_values(array_keys($yearsMap));

        $perYearTotals = [];
        foreach ($availableYears as $yr) {
            $rowsForYear = array_filter($filtered, function ($rr) use ($yr) {
                return isset($rr['year_of_moa']) && trim((string)$rr['year_of_moa']) === (string)$yr;
            });

            $truthy = function ($v) { return (is_bool($v)) ? $v : (strtoupper(trim((string)$v)) === 'TRUE'); };

            $moaY = array_reduce($rowsForYear, function ($acc, $r) use ($truthy) { return $acc + ($truthy($r['with_moa'] ?? null) ? 1 : 0); }, 0);
            $exprY = array_reduce($rowsForYear, function ($acc, $r) use ($truthy) { return $acc + ($truthy($r['with_expr'] ?? null) ? 1 : 0); }, 0);
            $resY = array_reduce($rowsForYear, function ($acc, $r) use ($truthy) { return $acc + ($truthy($r['with_res'] ?? null) ? 1 : 0); }, 0);

            // Uploaded STs per year: query StsAttachment table for matching composite keys
            $uploadedY = 0;
            try {
                $filteredKeysYear = [];
                foreach ($rowsForYear as $r) {
                    $k = implode('|', [ $r['region'] ?? null, $r['province'] ?? null, $r['municipality'] ?? ($r['city'] ?? null), $r['title'] ?? null, $r['year_of_moa'] ?? null ]);
                    $filteredKeysYear[$k] = true;
                }
                if (!empty($filteredKeysYear)) {
                    $attachmentsQuery = \App\Models\StsAttachment::query();
                    $attachmentsQuery->where('action', 'added');
                    $attachmentsQuery->where(function ($q) use ($rowsForYear) {
                        foreach ($rowsForYear as $row) {
                            $q->orWhere(function ($sub) use ($row) {
                                $sub->where('region', $row['region'] ?? null)
                                    ->where('province', $row['province'] ?? null)
                                    ->where('municipality', $row['municipality'] ?? ($row['city'] ?? null))
                                    ->where('title', $row['title'] ?? null)
                                    ->where('year_of_moa', $row['year_of_moa'] ?? null);
                            });
                        }
                    });
                    $attachments = $attachmentsQuery->get();

                    $attachmentKeys = [];
                    foreach ($attachments as $att) {
                        $key = implode('|', [ $att->region, $att->province, $att->municipality, $att->title, $att->year_of_moa ]);
                        if (isset($filteredKeysYear[$key])) $attachmentKeys[$key] = true;
                    }
                    $uploadedY = count($attachmentKeys);
                }
            } catch (\Throwable $ex) {
                $uploadedY = 0;
            }

            $perYearTotals[$yr] = [ (int)$moaY, (int)$uploadedY, (int)$exprY, (int)$resY ];
        }

        return response()->json([
            'provinces' => $provinces,
            'grouped' => $grouped,
            'allRows' => $filtered,
            'uploadedCount' => $uploadedCount,
            'availableYears' => $availableYears,
            'perYearTotals' => $perYearTotals,
        ]);
    }
}
