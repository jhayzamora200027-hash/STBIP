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
            return ['data' => [], 'regions' => [], 'headers' => []];
        }

        $parsed = $stsReportController->getParsedData($path);

        // return headers too – callers that don't need them can ignore it
        return [
            'data' => $parsed['data'] ?? [],
            'regions' => $parsed['regions'] ?? [],
            'headers' => $parsed['headers'] ?? [],
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
        $regionTextRaw = trim($row['region'] ?? '');
        $regionText = strtolower($regionTextRaw);
        $province = strtolower(trim($row['province'] ?? ''));

        if ($regionText === '') {
            return null;
        }

        // if the sheet simply uses a number ("5"), map to the canonical
        // Region roman form so filtering works as expected
        if (ctype_digit($regionText)) {
            $num = (int)$regionText;
            $map = [
                1 => 'Region I', 2 => 'Region II', 3 => 'Region III',
                4 => 'Region IV-A', 5 => 'Region V', 6 => 'Region VI',
                7 => 'Region VII', 8 => 'Region VIII', 9 => 'Region IX',
                10 => 'Region X', 11 => 'Region XI', 12 => 'Region XII',
                13 => 'Region XIII',
            ];
            if (isset($map[$num])) {
                return $map[$num];
            }
        }

        // Named-region aliases (case-insensitive)
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

        // Roman numeral fallback (match anywhere in the string)
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
            if (preg_match($re, $regionText)) {
                return $code;
            }
        }

        // as a last resort, return the original region string trimmed so that
        // exact-text filters still match.  this handles cases where the
        // spreadsheet uses an unusual variation not covered above.
        return $regionTextRaw !== '' ? $regionTextRaw : null;
    }

    public function ajaxRegionTitles(Request $request)
    {
        $all = $this->getAllData();
        $region = $request->input('region_image');

        $filteredData = $all['data'];

        if (!empty($region)) {
            $filteredData = array_filter($all['data'], function ($row) use ($region) {
                $title = trim($row['title'] ?? '');
                if ($title === '') {
                    return false; // discard whitespace-only titles early
                }
                $code = $this->inferRegionCodeFromRow($row);
                if ($code !== null && $code === $region) {
                    return true;
                }
                $raw = strtolower(trim($row['region'] ?? ''));
                return $raw !== '' && strpos($raw, strtolower($region)) !== false;
            });
        }

        return view('partials.st_listing_rows', [
            'filteredData' => $filteredData,
        ]);
    }

    /**
     * Return hierarchical JSON for modal dropdowns: provinces -> cities -> ST rows
     */
    /**
     * Build a province/city/ST hierarchy from a set of ST rows.
     *
     * Each row must contain at least `province` and `municipality` keys; empty
     * values are normalised to the string "UNKNOWN" so callers can detect them.
     * The returned structure mirrors the JSON returned by the former
     * `ajaxRegionHierarchy` endpoint, but is also useful for any future API.
     */
    protected function buildHierarchy(array $rows): array
    {
        $grouped = [];
        $provinces = [];

        foreach ($rows as $row) {
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

        return [$provinces, $grouped];
    }

    public function ajaxRegionHierarchy(Request $request)
    {
        $all = $this->getAllData();
        $region = $request->input('region_image');

        // short‑term cache keyed by region|province to speed up repeated clicks
        $cacheKey = 'region_hierarchy_' . md5(($region ?: '') . '|' . ($request->input('province') ?: ''));
        if (\Cache::has($cacheKey)) {
            return response()->json(\Cache::get($cacheKey));
        }

        $filtered = $all['data'];
        // determine status column indexes from headers (if available)
        $headersArr = $all['headers'] ?? [];
        $idxOng = null;
        $idxDis = null;
        foreach ($headersArr as $i => $h) {
            if ($idxOng === null && stripos($h, 'ongoing') !== false) {
                $idxOng = $i;
            }
            if ($idxDis === null && (stripos($h, 'dissolved') !== false || stripos($h, 'inactive') !== false)) {
                $idxDis = $i;
            }
        }
        $province = $request->input('province');
        if (!empty($region)) {
            $filtered = array_filter($filtered, function ($row) use ($region) {
                // sanitize title/municipality for trimming tests as well
                $clean = function($s) { return preg_replace('/[\x00-\x1F\x7F]+/u','', (string)$s); };
                $row['title'] = trim($clean($row['title'] ?? ''));
                $code = $this->inferRegionCodeFromRow($row);
                if ($code !== null && $code === $region) {
                    return true;
                }
                $raw = strtolower(trim($row['region'] ?? ''));
                return $raw !== '' && strpos($raw, strtolower($region)) !== false;
            });
        }
        if (!empty($province)) {
            $filtered = array_filter($filtered, function($r) use ($province) {
                return strtolower(trim($r['province'] ?? '')) === strtolower(trim($province));
            });
        }

        // remove rows that don't contain any useful information.  we also
        // drop any record whose title is blank after trimming – the spreadsheet
        // sometimes contains rows filled with spaces which should not show up
        // as "(no title)" entries in the UI.
        $filtered = array_values(array_filter($filtered, function($r) {
            // strip control characters before examining the title
            $clean = function($s) { return preg_replace('/[\x00-\x1F\x7F]+/u','', (string)$s); };
            $title = trim($clean($r['title'] ?? ''));
            if ($title === '') {
                return false;
            }
            return trim($clean($r['province'] ?? '')) !== ''
                || trim($clean($r['municipality'] ?? $r['city'] ?? '')) !== '';
        }));

        // attach computed status string to each remaining row so client code
        // can simply look at r.status. mimic MainReportController logic.
        if (!empty($headersArr)) {
            $truthy = function ($v) {
                if (is_bool($v)) return $v;
                $s = strtolower(trim((string)$v));
                return in_array($s, ['1','true','yes','y','✓'], true);
            };
            foreach ($filtered as &$r) {
                $st = '';
                if (!empty($r['status'])) {
                    $st = strtolower($r['status']);
                }
                if (!$st && $idxOng !== null && isset($r['row'][$idxOng]) && $truthy($r['row'][$idxOng])) {
                    $st = 'ongoing';
                }
                if (!$st && $idxDis !== null && isset($r['row'][$idxDis]) && $truthy($r['row'][$idxDis])) {
                    $st = 'dissolved';
                }
                $r['status'] = $st;
            }
            unset($r);
        }

        list($provinces, $grouped) = $this->buildHierarchy($filtered);

        // compute attachment/year summaries (unchanged from previous logic)
        $uploadedCount = 0;
        try {
            if (!empty($region)) {
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

                $attachmentsQuery = \App\Models\StsAttachment::query();
                $attachmentsQuery->where('action', 'added');
                $keysList = array_keys($filteredKeys);
                if (!empty($keysList)) {
                    $attachmentsQuery->whereIn(
                        \DB::raw("CONCAT(region,'|',province,'|',municipality,'|',title,'|',year_of_moa)"),
                        $keysList
                    );
                }
                $attachments = $attachmentsQuery->get();
                $uploadedCount = $attachments->count();
            }
        } catch (\Throwable $ex) {
            $uploadedCount = 0;
        }

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
                    $keysListYear = array_keys($filteredKeysYear);
                    if (!empty($keysListYear)) {
                        $attachmentsQuery->whereIn(
                            \DB::raw("CONCAT(region,'|',province,'|',municipality,'|',title,'|',year_of_moa)"),
                            $keysListYear
                        );
                    }
                    $attachments = $attachmentsQuery->get();

                    $uploadedY = $attachments->count();
                }
            } catch (\Throwable $ex) {
                $uploadedY = 0;
            }

            $perYearTotals[$yr] = [ (int)$moaY, (int)$uploadedY, (int)$exprY, (int)$resY ];
        }

        $result = [
            'provinces' => $provinces,
            'grouped' => $grouped,
            'allRows' => $filtered,
            'headers' => $headersArr,
            'uploadedCount' => $uploadedCount,
            'availableYears' => $availableYears,
            'perYearTotals' => $perYearTotals,
        ];
        // cache for a short duration to reduce repeated computation
        try { \Cache::put($cacheKey, $result, now()->addMinutes(30)); } catch (\Throwable $e) {}
        return response()->json($result);
    }
}
