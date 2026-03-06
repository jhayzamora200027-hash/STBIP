<?php
namespace App\Http\Controllers;
use Illuminate\Http\Request;
use Maatwebsite\Excel\Facades\Excel;
use PhpOffice\PhpSpreadsheet\IOFactory;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Cache;
use App\Models\Uploadlog;
use Illuminate\Support\Facades\Auth;
use Illuminate\Pagination\LengthAwarePaginator;
use App\Http\Controllers\Controller;

class ExcelController extends Controller
{

    public function setBase(Request $request)
    {
        $storedFilename = $request->input('base_excel');
        try {
            if (!$storedFilename) {
                return redirect()->back()->with('error', 'No Excel file selected.');
            }
            $excelDir = storage_path('app/excels');
            $full = $excelDir . '/' . $storedFilename;
            if (!file_exists($full)) {
                return redirect()->back()->with('error', 'Selected file does not exist.');
            }
            session(['base_excel' => $storedFilename]);
            $userId = Auth::check() ? Auth::user()->user_id ?? Auth::user()->id : null;

            $docno = \App\Models\Uploadlog::where('excelname', $storedFilename)
                        ->orderBy('created_at', 'desc')
                        ->value('docno');

            $log = \App\Models\Selectdocslogs::create([
                'createdby' => $userId,
                'excelname' => $storedFilename,
                'actionlogs' => 'select',
                'docselected' => $docno,
                'created_at' => now(),
                'updated_at' => now(),
            ]);
            if (!$log) {
                return redirect()->back()->with('error', 'Failed to log base Excel update.');
            }
            return redirect()->back()->with('success', 'Base Excel file updated successfully.');
        } catch (\Exception $e) {
            return redirect()->back()->with('error', 'Error updating base Excel: ' . $e->getMessage());
        }
    }
    public function uploadLogs(Request $request)
    {
        $excelDir = storage_path('app/excels');

        $allLogs = Uploadlog::orderBy('created_at', 'desc')->get();

        $filtered = $allLogs->filter(function ($log) use ($excelDir) {
            if (!$log->excelname) {
                return false;
            }
            $possible = $log->excelname;
            $full = $excelDir . '/' . $possible;
            if (!file_exists($full)) {
                return false;
            }
            $log->stored_filename = $possible;
            return true;
        })->values();

        $perPage = 10;
        $currentPage = (int) ($request->input('page', 1));
        if ($currentPage < 1) {
            $currentPage = 1;
        }

        $total = $filtered->count();
        $offset = ($currentPage - 1) * $perPage;
        $itemsForPage = $filtered->slice($offset, $perPage)->values();

        $logs = new LengthAwarePaginator(
            $itemsForPage,
            $total,
            $perPage,
            $currentPage,
            [
                'path' => $request->url(),
                'query' => $request->query(),
            ]
        );

        $currentSheetUrl = null;
        $baseExcel = null;
        $selectQuery = \App\Models\Selectdocslogs::orderBy('updated_at', 'desc');
        if ($request->filled('from_date')) {
            $selectQuery->whereDate('updated_at', '>=', $request->input('from_date'));
        }
        if ($request->filled('to_date')) {
            $selectQuery->whereDate('updated_at', '<=', $request->input('to_date'));
        }
        $selectLog = $selectQuery->get();

        foreach ($selectLog as $entry) {
            if ($entry->excelname && trim($entry->excelname) !== '') {
                $full = $excelDir . '/' . $entry->excelname;
                if (file_exists($full) && is_readable($full) && is_file($full)) {
                    $baseExcel = $entry->excelname;
                    break;
                }
            }
        }
        if ($baseExcel && preg_match('/^gsheet_([a-zA-Z0-9-_]+)_/', $baseExcel, $matches)) {
            $spreadsheetId = $matches[1];
            $currentSheetUrl = 'https://docs.google.com/spreadsheets/d/' . $spreadsheetId . '/edit';
        }
        $latestSelection = \App\Models\Selectdocslogs::orderBy('updated_at', 'desc')->first();
        $latestUpdatedBy = $latestSelection ? $latestSelection->createdby : null;
        $latestActionLog = $latestSelection ? $latestSelection->actionlogs : null;

        $selectLogs = $selectLog;

        if (!session()->has('base_excel') && $baseExcel) {
            session(['base_excel' => $baseExcel]);
        }

        if ($request->ajax()) {
            $html = view('dashboard.maincomponents.partials.uploadingdocument_logs', compact('logs', 'currentSheetUrl', 'latestUpdatedBy', 'latestActionLog', 'latestSelection', 'selectLogs'))->render();
            return response()->json(['html' => $html]);
        }

        return view('dashboard.maincomponents.uploadingdocument', compact('logs', 'currentSheetUrl', 'latestUpdatedBy', 'latestActionLog', 'latestSelection', 'selectLogs'));
    }

    public function uploadmoasts(Request $request)
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
            $cacheKey = 'uploadmoasts_rows_' . md5($path . '|' . filemtime($path));

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

                    if ($titleIdx === false || $provinceIdx === false || $municipalityIdx === false) {
                        continue;
                    }

                    foreach (array_slice($rowsArray, 1) as $row) {
                        $title = trim($row[$titleIdx] ?? '');
                        if ($title === '') {
                            continue;
                        }
                        $rowsLocal[] = [
                            'region' => $sheetName,
                            'title' => $title,
                            'province' => trim($row[$provinceIdx] ?? ''),
                            'municipality' => trim($row[$municipalityIdx] ?? ''),
                            'year_of_moa' => $yearIdx !== false ? trim($row[$yearIdx] ?? '') : null,
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
        foreach ($rows as $entry) {
            $title = $entry['title'] ?? '';
            $region = $entry['region'] ?? '';
            if ($title === '' || $region === '') {
                continue;
            }
            $allTitles[$title] = true;
            if (!isset($regionTitleMap[$region])) {
                $regionTitleMap[$region] = [];
            }
            $regionTitleMap[$region][$title] = true;
        }

        $titles = array_keys($allTitles);
        sort($titles, SORT_NATURAL | SORT_FLAG_CASE);
        foreach ($regionTitleMap as $region => $titleSet) {
            $list = array_keys($titleSet);
            sort($list, SORT_NATURAL | SORT_FLAG_CASE);
            $regionTitleMap[$region] = $list;
        }

        $selectedRegion = $request->input('region');
        if ($selectedRegion) {
            $filtered = array_values(array_filter($rows, function ($row) use ($selectedRegion) {
                return $row['region'] === $selectedRegion;
            }));
        } else {
            $filtered = $rows;
        }

        $searchTitle = trim((string) $request->input('title', ''));
        if ($searchTitle !== '') {
            $filtered = array_values(array_filter($filtered, function ($row) use ($searchTitle) {
                return isset($row['title']) && $row['title'] === $searchTitle;
            }));
        }

        $perPage = 10;
        $currentPage = max(1, (int) $request->input('page', 1));
        $total = count($filtered);
        $offset = ($currentPage - 1) * $perPage;
        $itemsForPage = array_slice($filtered, $offset, $perPage);

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
            ])->render();

            return response()->json(['html' => $html]);
        }

        return view('dashboard.maincomponents.STsManager', [
            'regions' => $regions,
            'selectedRegion' => $selectedRegion,
            'searchTitle' => $searchTitle,
            'titles' => $titles,
            'regionTitleMap' => $regionTitleMap,
            'sts' => $sts,
        ]);
    }
    public function upload(Request $request)
    {
        $request->validate([
            'excelFile' => 'nullable|file|mimes:xlsx,xls|required_without:googleSheetUrl',
            'googleSheetUrl' => 'nullable|url|required_without:excelFile',
        ]);

        $excelDir = storage_path('app/excels');
        if (!is_dir($excelDir)) {
            mkdir($excelDir, 0775, true);
        }

        $storedFilename = null;
        $path = null;
        $fullPath = null;
        $lastDocno = Uploadlog::max('docno');
        $nextNumber = $lastDocno ? ((int) $lastDocno) + 1 : 1;
        $nextDocno = str_pad($nextNumber, 7, '0', STR_PAD_LEFT);

        if ($request->filled('googleSheetUrl')) {
            $googleUrl = $request->input('googleSheetUrl');

            if (!preg_match('#/spreadsheets/d/([a-zA-Z0-9-_]+)#', $googleUrl, $matches)) {
                return redirect()->back()->with('error', 'Invalid Google Sheets URL format.');
            }

            $spreadsheetId = $matches[1];
            $exportUrl = "https://docs.google.com/spreadsheets/d/{$spreadsheetId}/export?format=xlsx";

            try {
                $response = Http::get($exportUrl);
            } catch (\Throwable $e) {
                return redirect()->back()->with('error', 'Error downloading Google Sheet. Please check that it is shared as "Anyone with the link" and try again.');
            }

            if (!$response->ok()) {
                $status = $response->status();
                if ($status === 401 || $status === 403) {
                    return redirect()->back()->with('error', 'The Google Sheet is not accessible. Please make sure it is shared as "Anyone with the link" and try again.');
                }
                return redirect()->back()->with('error', 'Failed to download Google Sheet (HTTP ' . $status . ').');
            }

            $storedFilename = 'gsheet_' . $spreadsheetId . '_' . time() . '.xlsx';
            $fullPath = $excelDir . '/' . $storedFilename;

            try {
                file_put_contents($fullPath, $response->body());
            } catch (\Throwable $e) {
                return redirect()->back()->with('error', 'Unable to save downloaded Google Sheet: ' . $e->getMessage());
            }

            $path = 'excels/' . $storedFilename;
        } else {
            $file = $request->file('excelFile');
            $path = $file->store('excels', 'local'); 
            $storedFilename = basename($path); 
            $fullPath = storage_path('app/' . $path);
        }

        $userId = Auth::check() ? Auth::user()->user_id ?? Auth::user()->id : null;
        Uploadlog::create([
            'createdby' => $userId,
            'excelname' => $storedFilename,
            'docno' => $nextDocno,
            'created_at' => now(),
            'updated_at' => now(),
        ]);
        session(['recent_excel' => $path]);

        if (!$fullPath || !file_exists($fullPath) || !is_readable($fullPath)) {
            return redirect()->back()->with('error', 'Excel file not found or not readable.');
        }

        $spreadsheet = IOFactory::load($fullPath);
        $sheetCounts = [];
        foreach ($spreadsheet->getSheetNames() as $sheetName) {
            $sheet = $spreadsheet->getSheetByName($sheetName);
            $rows = $sheet->toArray();
            if (count($rows) < 1) {
                $sheetCounts[$sheetName] = 0;
                continue;
            }
            $header = $rows[0];
            $titleOfStIndex = array_search('Title of ST', $header);
            if ($titleOfStIndex === false) {
                $sheetCounts[$sheetName] = 0;
                continue;
            }
            $dataRows = array_slice($rows, 1);
            $count = 0;
            foreach ($dataRows as $row) {
                if (isset($row[$titleOfStIndex]) && trim($row[$titleOfStIndex]) !== '') {
                    $count++;
                }
            }
            $sheetCounts[$sheetName] = $count;
        }
        if ($request->ajax()) {
            return response()->json(['recent_excel' => $path]);
        }

        return redirect()->back()->with('sheetCounts', $sheetCounts);
    }


public function chartData(Request $request)
{
    $excelDir = storage_path('app/excels');
    if (!is_dir($excelDir)) {
        return response()->json(['sheetCounts' => [], 'error' => 'Excel directory does not exist.']);
    }

    $baseExcel = null;
    $selectLog = \App\Models\Selectdocslogs::orderBy('updated_at', 'desc')->get();
    foreach ($selectLog as $log) {
        if ($log->excelname && trim($log->excelname) !== '') {
            $full = storage_path('app/excels/' . $log->excelname);
            if (file_exists($full) && is_readable($full) && is_file($full)) {
                $baseExcel = $log->excelname;
                break;
            }
        }
    }
    $path = null;
    if ($baseExcel) {
        $path = 'excels/' . $baseExcel;
    }
    if (!$path) {
        $xlsxFiles = glob($excelDir . '/*.xlsx') ?: [];
        $xlsFiles = glob($excelDir . '/*.xls') ?: [];
        $files = array_merge($xlsxFiles, $xlsFiles);
        if ($files && count($files) > 0) {
            usort($files, function($a, $b) {
                return filemtime($b) - filemtime($a);
            });
            $latestFile = $files[0];
            $path = 'excels/' . basename($latestFile);
        } else {
            return response()->json(['sheetCounts' => [], 'error' => 'No Excel files found.']);
        }
    }

    $fullPath = storage_path('app/' . $path);
    if (!file_exists($fullPath) || !is_readable($fullPath)) {
        return response()->json(['sheetCounts' => [], 'error' => 'Excel file not found or not readable.']);
    }
    $cacheKey = 'chartData_' . md5($fullPath . '|' . filemtime($fullPath));

    $sheetCounts = Cache::remember($cacheKey, 3600, function () use ($fullPath) {
        $spreadsheet = \PhpOffice\PhpSpreadsheet\IOFactory::load($fullPath);
        $sheetCountsLocal = [];
        $sheetNames = $spreadsheet->getSheetNames();

        foreach ($sheetNames as $sheetName) {
            $sheet = $spreadsheet->getSheetByName($sheetName);
            $rows = $sheet->toArray();
            if (count($rows) < 1) {
                $sheetCountsLocal[$sheetName] = 0;
                continue;
            }
            $header = $rows[0];
            $titleOfStIndex = array_search('Title of ST', $header);
            if ($titleOfStIndex === false) {
                $sheetCountsLocal[$sheetName] = 0;
                continue;
            }
            $dataRows = array_slice($rows, 1);
            $count = 0;
            foreach ($dataRows as $row) {
                if (isset($row[$titleOfStIndex]) && trim($row[$titleOfStIndex]) !== '') {
                    $count++;
                }
            }
            $sheetCountsLocal[$sheetName] = $count;
        }

        return $sheetCountsLocal;
    });
    return response()->json(['sheetCounts' => $sheetCounts, 'path' => $path]);
}

    public function chartCategoriesByTitle(Request $request)
    {
        try {
            $excelDir = storage_path('app/excels');
            if (!is_dir($excelDir)) {
                Log::error('Excel directory does not exist: ' . $excelDir);
                return response()->json(['categories' => [], 'error' => 'Excel directory does not exist.']);
            }
            $baseExcel = null;
            $selectLog = \App\Models\Selectdocslogs::orderBy('updated_at', 'desc')->get();
            foreach ($selectLog as $log) {
                if ($log->excelname && trim($log->excelname) !== '') {
                    $full = storage_path('app/excels/' . $log->excelname);
                    if (file_exists($full) && is_readable($full) && is_file($full)) {
                        $baseExcel = $log->excelname;
                        break;
                    }
                }
            }
            $path = null;
            if ($baseExcel) {
                $path = 'excels/' . $baseExcel;
            }
            if (!$path) {
                $xlsxFiles = glob($excelDir . '/*.xlsx') ?: [];
                $xlsFiles = glob($excelDir . '/*.xls') ?: [];
                $files = array_merge($xlsxFiles, $xlsFiles);
                if ($files && count($files) > 0) {
                    usort($files, function($a, $b) {
                        return filemtime($b) - filemtime($a);
                    });
                    $latestFile = $files[0];
                    $path = 'excels/' . basename($latestFile);
                } else {
                    Log::error('No Excel files found in directory: ' . $excelDir);
                    return response()->json(['categories' => [], 'error' => 'No Excel files found.']);
                }
            }

            $fullPath = storage_path('app/' . $path);
            if (!file_exists($fullPath) || !is_readable($fullPath)) {
                Log::error('Excel file not found or not readable: ' . $fullPath);
                return response()->json(['categories' => [], 'error' => 'Excel file not found or not readable.']);
            }

            $spreadsheet = \PhpOffice\PhpSpreadsheet\IOFactory::load($fullPath);
            $categoryCounts = [];
            $selectedTabs = $request->input('tabs', []);
            $selectedTabs = array_map('strval', $selectedTabs);
            foreach ($spreadsheet->getSheetNames() as $sheetName) {
                if (stripos($sheetName, 'Data CY 2020-2022') !== false) continue;
                if (!empty($selectedTabs) && !in_array($sheetName, $selectedTabs, true)) continue;
                $sheet = $spreadsheet->getSheetByName($sheetName);
                $rowIterator = $sheet->getRowIterator();
                $titleOfStIndex = null;
                foreach ($rowIterator as $rowIndex => $row) {
                    $cellIterator = $row->getCellIterator();
                    $cellIterator->setIterateOnlyExistingCells(false);
                    $rowData = [];
                    foreach ($cellIterator as $cell) {
                        $rowData[] = $cell->getValue();
                    }
                    if ($rowIndex == 1) {
                        // Header row
                        $titleOfStIndex = array_search('Title of ST', $rowData);
                        if ($titleOfStIndex === false) break;
                        continue;
                    }
                    if ($titleOfStIndex !== null && isset($rowData[$titleOfStIndex])) {
                        $title = trim($rowData[$titleOfStIndex]);
                        if ($title !== '') {
                            $categoryCounts[$title] = ($categoryCounts[$title] ?? 0) + 1;
                        }
                    }
                }
            }
            return response()->json(['categories' => $categoryCounts, 'path' => $path]);
        } catch (\Throwable $e) {
            Log::error('chartCategoriesByTitle error: ' . $e->getMessage() . "\n" . $e->getTraceAsString());
            return response()->json(['categories' => [], 'error' => 'Server error: ' . $e->getMessage()]);
        }
    }

    public function refreshFromGoogleSheet(Request $request)
    {
        $excelDir = storage_path('app/excels');
        if (!is_dir($excelDir)) {
            return redirect()->back()->with('error', 'No Excel directory found to refresh.');
        }

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

        if (!$baseExcel) {
            return redirect()->back()->with('error', 'No base Excel file is currently selected to refresh.');
        }

        if (!preg_match('/^gsheet_([a-zA-Z0-9-_]+)_/', $baseExcel, $matches)) {
            return redirect()->back()->with('error', 'Current base file was not created from a Google Sheet upload.');
        }

        $spreadsheetId = $matches[1];
        $exportUrl = "https://docs.google.com/spreadsheets/d/{$spreadsheetId}/export?format=xlsx";

        try {
            $response = Http::get($exportUrl);
        } catch (\Throwable $e) {
            return redirect()->back()->with('error', 'Error downloading Google Sheet. Please check that it is shared as "Anyone with the link" and try again.');
        }

        if (!$response->ok()) {
            $status = $response->status();
            if ($status === 401 || $status === 403) {
                return redirect()->back()->with('error', 'The Google Sheet is not accessible. Please make sure it is shared as "Anyone with the link" and try again.');
            }
            return redirect()->back()->with('error', 'Failed to download Google Sheet (HTTP ' . $status . ').');
        }

        $storedFilename = 'gsheet_' . $spreadsheetId . '_' . time() . '.xlsx';
        $fullPath = $excelDir . '/' . $storedFilename;

        try {
            file_put_contents($fullPath, $response->body());
        } catch (\Throwable $e) {
            return redirect()->back()->with('error', 'Unable to save refreshed Google Sheet: ' . $e->getMessage());
        }

        $path = 'excels/' . $storedFilename;

        $lastDocno = Uploadlog::max('docno');
        $nextNumber = $lastDocno ? ((int) $lastDocno) + 1 : 1;
        $nextDocno = str_pad($nextNumber, 7, '0', STR_PAD_LEFT);

        $userId = Auth::check() ? Auth::user()->user_id ?? Auth::user()->id : null;
        Uploadlog::create([
            'createdby' => $userId,
            'excelname' => $storedFilename,
            'docno' => $nextDocno,
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        session(['base_excel' => $storedFilename]);
        \App\Models\Selectdocslogs::create([
            'createdby' => $userId,
            'excelname' => $storedFilename,
            'actionlogs' => 'refresh',            'docselected' => $nextDocno,            'created_at' => now(),
            'updated_at' => now(),
        ]);

        return redirect()->back()->with('success', 'Base Excel file refreshed from Google Sheet.');
    }

}