<?php

namespace App\Http\Controllers;

use App\Models\SocialTechnologyTitle;
use App\Models\Uploadlog;
use App\Models\Selectdocslogs;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Http;
use PhpOffice\PhpSpreadsheet\IOFactory;

class SocialTechnologyController extends Controller
{
    public function index(Request $request)
    {
        $titles = SocialTechnologyTitle::query()->latest('updated_at')->get();

        return view('dashboard.maincomponents.social_technologies', [
            'titles' => $titles,
        ]);
    }

    public function import(Request $request)
    {
        $request->validate([
            'csv_file' => ['nullable', 'file'],
            'google_sheet_url' => ['nullable', 'url'],
            'stored_excel' => ['nullable', 'string'],
        ]);

        $added = 0;
        DB::beginTransaction();
        try {
            $titles = [];

            // If Google Sheet URL provided, download and store as XLSX
            $storedFilename = null;
            $excelDir = storage_path('app/excels');
            if (!is_dir($excelDir)) {
                mkdir($excelDir, 0775, true);
            }

            if ($request->filled('google_sheet_url')) {
                $googleUrl = $request->input('google_sheet_url');
                if (!preg_match('#/spreadsheets/d/([a-zA-Z0-9-_]+)#', $googleUrl, $matches)) {
                    throw new \RuntimeException('Invalid Google Sheets URL format.');
                }
                $spreadsheetId = $matches[1];
                $exportUrl = 'https://docs.google.com/spreadsheets/d/' . $spreadsheetId . '/export?format=xlsx';
                $response = Http::get($exportUrl);
                if (!$response->ok()) {
                    throw new \RuntimeException('Failed to download Google Sheet (HTTP ' . $response->status() . ').');
                }
                $storedFilename = 'gsheet_' . $spreadsheetId . '_' . time() . '.xlsx';
                $fullPath = $excelDir . DIRECTORY_SEPARATOR . $storedFilename;
                file_put_contents($fullPath, $response->body());

                // log upload
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
                    'actionlogs' => 'import-to-socialtech',
                    'docselected' => $nextDocno,
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);

                $filePath = $fullPath;
            } elseif ($request->filled('stored_excel')) {
                $filePath = storage_path('app/excels/' . basename($request->input('stored_excel')));
            } elseif ($request->file('csv_file')) {
                $file = $request->file('csv_file');
                $handle = fopen($file->getRealPath(), 'r');
                if ($handle !== false) {
                    while (($row = fgetcsv($handle)) !== false) {
                        $title = trim((string) ($row[0] ?? ''));
                        if ($title === '') continue;
                        $titles[] = $title;
                    }
                    fclose($handle);
                }
                $filePath = null;
            } else {
                $filePath = null;
            }

            // If an Excel path is available, parse XLSX for titles
            if (!empty($filePath) && file_exists($filePath)) {
                $reader = IOFactory::createReaderForFile($filePath);
                $reader->setReadDataOnly(true);
                $spreadsheet = $reader->load($filePath);
                foreach ($spreadsheet->getSheetNames() as $sheetName) {
                    $sheet = $spreadsheet->getSheetByName($sheetName);
                    $rows = $sheet->toArray(null, false, false, false);
                    if (count($rows) < 1) continue;

                    $header = array_map(fn($h) => strtolower(trim((string) $h)), $rows[0]);
                    $titleIdx = array_search('title of st', $header);
                    if ($titleIdx === false) {
                        $titleIdx = array_search('title', $header);
                    }

                    $hasHeader = $titleIdx !== false;

                    // If no explicit header found, fall back to first column (index 0)
                    if ($titleIdx === false) {
                        $titleIdx = 0;
                    }

                    $dataRows = $hasHeader ? array_slice($rows, 1) : $rows;

                    foreach ($dataRows as $row) {
                        $title = trim((string) ($row[$titleIdx] ?? ''));
                        if ($title === '') continue;
                        $titles[] = $title;
                    }
                }
            }

            // Insert titles, avoiding exact duplicates
            foreach (array_values(array_unique($titles)) as $title) {
                if ($title === '') continue;
                $exists = SocialTechnologyTitle::query()->where('title', $title)->exists();
                if ($exists) continue;
                SocialTechnologyTitle::create([
                    'title' => $title,
                    'createdby' => Auth::check() ? Auth::user()->name : null,
                    'updatedby' => Auth::check() ? Auth::user()->name : null,
                ]);
                $added++;
            }

            DB::commit();
            return redirect()->route('socialtech.index')->with('status', "Imported $added titles.");
        } catch (\Throwable $e) {
            DB::rollBack();
            return redirect()->route('socialtech.index')->with('error', $e->getMessage());
        }
    }

    public function add(Request $request)
    {
        $request->validate([
            'titles' => ['nullable', 'array'],
            'titles.*' => ['required', 'string', 'max:1000'],
            'title' => ['nullable', 'string', 'max:1000'],
        ]);

        $submitted = [];
        if ($request->filled('titles')) {
            $submitted = array_map(fn($v) => trim((string) $v), $request->input('titles'));
        } elseif ($request->filled('title')) {
            $submitted = [trim($request->input('title'))];
        }

        $submitted = array_values(array_filter(array_unique($submitted), fn($v) => $v !== ''));
        if (count($submitted) === 0) {
            return redirect()->route('socialtech.index')->with('error', 'No title provided.');
        }

        $added = 0;
        foreach ($submitted as $title) {
            $exists = SocialTechnologyTitle::query()->where('title', $title)->exists();
            if ($exists) continue;
            SocialTechnologyTitle::create([
                'title' => $title,
                'createdby' => Auth::check() ? Auth::user()->name : null,
                'updatedby' => Auth::check() ? Auth::user()->name : null,
            ]);
            $added++;
        }

        if ($added === 0) {
            return redirect()->route('socialtech.index')->with('status', 'No new titles were added (all existed already).');
        }

        return redirect()->route('socialtech.index')->with('status', "Added $added title(s).");
    }
}
