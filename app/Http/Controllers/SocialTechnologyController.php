<?php

namespace App\Http\Controllers;

use App\Models\SocialTechnologyTitle;
use App\Models\Uploadlog;
use App\Models\Selectdocslogs;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use PhpOffice\PhpSpreadsheet\IOFactory;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;

class SocialTechnologyController extends Controller
{
    public function index(Request $request)
    {
        $query = SocialTechnologyTitle::query();

        if ($request->filled('social_technology')) {
            $term = trim((string) $request->input('social_technology'));
            if ($term !== '') {
                $query->where('social_technology', 'like', '%' . $term . '%');
            }
        }

        $titles = $query->latest('updated_at')->paginate(10)->withQueryString();

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
        $updated = 0;
        DB::beginTransaction();
        try {
            $inputRows = [];

            $storedFilename = null;
            $excelDir = storage_path('app/excels');
            if (!is_dir($excelDir)) {
                mkdir($excelDir, 0775, true);
            }

            $filePath = null;
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
                $ext = strtolower($file->getClientOriginalExtension() ?: '');
                if (in_array($ext, ['xlsx', 'xls'])) {
                    $storedFilename = 'upload_' . time() . '.' . $ext;
                    $fullPath = $excelDir . DIRECTORY_SEPARATOR . $storedFilename;
                    $file->move($excelDir, $storedFilename);
                    $filePath = $fullPath;
                } else {
                    $handle = fopen($file->getRealPath(), 'r');
                    if ($handle !== false) {
                        while (($row = fgetcsv($handle)) !== false) {
                            $inputRows[] = $row;
                        }
                        fclose($handle);
                    }
                    $filePath = null;
                }
            }

            if (!empty($filePath) && file_exists($filePath)) {
                $reader = IOFactory::createReaderForFile($filePath);
                $reader->setReadDataOnly(true);
                $spreadsheet = $reader->load($filePath);
                foreach ($spreadsheet->getSheetNames() as $sheetName) {
                    $sheet = $spreadsheet->getSheetByName($sheetName);
                    $rows = $sheet->toArray(null, false, false, false);
                    if (count($rows) < 1) continue;
                    foreach ($rows as $r) {
                        $inputRows[] = $r;
                    }
                }
            }

            if (count($inputRows) === 0) {
                DB::commit();
                    return redirect()->route('STDashboard')->with('status', 'No rows found in uploaded file.');
            }

            $rawHeader = array_map(fn($h) => strtolower(trim((string) $h)), $inputRows[0]);
            $fieldMap = [];
            $canonical = [
                'sector' => 'sector',
                'laws_and_issuances' => 'laws_and_issuances',
                'social_technology' => 'social_technology',
                'description' => 'description',
                'objectives' => 'objectives',
                'components' => 'components',
                'pilot_areas' => 'pilot_areas',
                'year_implemented' => 'year_implemented',
                'status_remarks' => 'status_remarks',
                'resolution' => 'resolution',
                'guidelines' => 'guidelines',
                'program_manual_outline' => 'program_manual_outline',
                'information_systems_developed' => 'information_systems_developed',
                'session_guide_key_topics' => 'session_guide_key_topics',
                'training_manual_outline' => 'training_manual_outline',
            ];

            foreach ($rawHeader as $idx => $h) {
                $norm = preg_replace('/[^a-z0-9]+/', '_', $h);
                $norm = trim($norm, '_');
                if (in_array($norm, ['title_of_st', 'title', 'social_technology', 'socialtechnology', 'social_technology'])) {
                    $fieldMap[$idx] = 'social_technology';
                    continue;
                }
                if (isset($canonical[$norm])) {
                    $fieldMap[$idx] = $canonical[$norm];
                    continue;
                }
                $alt = str_replace(' ', '_', $h);
                $alt = preg_replace('/[^a-z0-9_]/', '', strtolower($alt));
                if (isset($canonical[$alt])) {
                    $fieldMap[$idx] = $canonical[$alt];
                }
            }

            $dataRows = array_slice($inputRows, 1);
            foreach ($dataRows as $row) {
                $rowData = [];
                foreach ($fieldMap as $colIdx => $fieldName) {
                    $rowData[$fieldName] = isset($row[$colIdx]) ? trim((string) $row[$colIdx]) : '';
                }

                $titleVal = trim((string) ($rowData['social_technology'] ?? ''));
                if ($titleVal === '') continue;

                if (array_key_exists('year_implemented', $rowData)) {
                    $y = trim((string) $rowData['year_implemented']);
                    if ($y === '') {
                        $rowData['year_implemented'] = null;
                    } else {
                        $rowData['year_implemented'] = $y;
                    }
                }

                $userName = Auth::check() ? Auth::user()->name : null;
                $existing = SocialTechnologyTitle::where('social_technology', $titleVal)->first();
                if ($existing) {
                    $existing->fill(array_merge($existing->toArray(), $rowData));
                    $existing->updatedby = $userName;
                    $existing->save();
                    $updated++;
                } else {
                    $createData = $rowData;
                    $createData['social_technology'] = $titleVal;
                    $createData['createdby'] = $userName;
                    $createData['updatedby'] = $userName;
                    SocialTechnologyTitle::create($createData);
                    $added++;
                }
            }

            DB::commit();
            $msg = "Imported $added new title(s)";
            if ($updated) $msg .= ", updated $updated existing";
            return redirect()->route('STDashboard')->with('status', $msg . '.');
        } catch (\Throwable $e) {
            DB::rollBack();
            return redirect()->route('STDashboard')->with('error', $e->getMessage());
        }
    }

    public function add(Request $request)
    {
        $request->validate([
            'social_technologies' => ['nullable', 'array'],
            'social_technologies.*' => ['required', 'string', 'max:1000'],
            'social_technology' => ['nullable', 'string', 'max:1000'],
        ]);

        $submitted = [];
        if ($request->filled('social_technologies')) {
            $submitted = array_map(fn($v) => trim((string) $v), $request->input('social_technologies'));
        } elseif ($request->filled('social_technology')) {
            $submitted = [trim($request->input('social_technology'))];
        }

        $submitted = array_values(array_filter(array_unique($submitted), fn($v) => $v !== ''));
        if (count($submitted) === 0) {
            return redirect()->route('STDashboard')->with('error', 'No title provided.');
        }

        $added = 0;
        foreach ($submitted as $title) {
            $exists = SocialTechnologyTitle::query()->where('social_technology', $title)->exists();
            if ($exists) continue;
            SocialTechnologyTitle::create([
                'social_technology' => $title,
                'createdby' => Auth::check() ? Auth::user()->name : null,
                'updatedby' => Auth::check() ? Auth::user()->name : null,
            ]);
            $added++;
        }

        if ($added === 0) {
            return redirect()->route('STDashboard')->with('status', 'No new titles were added (all existed already).');
        }

        return redirect()->route('STDashboard')->with('status', "Added $added title(s).");
    }

    public function export(Request $request)
    {
        $titles = SocialTechnologyTitle::query()->latest('updated_at')->get();

        $data = [];
        $data[] = [
            'Sector',
            'Laws and Issuances',
            'Social Technology',
            'Description',
            'Objectives',
            'Components',
            'Pilot Areas',
            'Year Implemented',
            'Status Remarks',
            'Resolution',
            'Guidelines',
            'Program Manual Outline',
            'Information Systems Developed',
            'Session Guide Key Topics',
            'Training Manual Outline',
        ];

        foreach ($titles as $t) {
            $data[] = [
                $t->sector ?? '',
                $t->laws_and_issuances ?? '',
                $t->social_technology ?? '',
                $t->description ?? '',
                $t->objectives ?? '',
                $t->components ?? '',
                $t->pilot_areas ?? '',
                $t->year_implemented ?? '',
                $t->status_remarks ?? '',
                $t->resolution ?? '',
                $t->guidelines ?? '',
                $t->program_manual_outline ?? '',
                $t->information_systems_developed ?? '',
                $t->session_guide_key_topics ?? '',
                $t->training_manual_outline ?? '',
            ];
        }

        $fileName = 'social_technology_titles_' . date('Ymd_His') . '.xlsx';

        $spreadsheet = new Spreadsheet();
        $sheet = $spreadsheet->getActiveSheet();
        $row = 1;
        foreach ($data as $r) {
            $sheet->fromArray($r, null, 'A' . $row, true);
            $row++;
        }

        $writer = new Xlsx($spreadsheet);

        return response()->streamDownload(function () use ($writer) {
            $writer->save('php://output');
        }, $fileName, [
            'Content-Type' => 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
        ]);
    }

    public function update(Request $request, $id)
    {
        $request->validate([
            'social_technology' => ['required', 'string', 'max:1000'],
            'sector' => ['nullable', 'string', 'max:255'],
            'laws_and_issuances' => ['nullable', 'string', 'max:2000'],
            'description' => ['nullable', 'string', 'max:5000'],
            'objectives' => ['nullable', 'string', 'max:5000'],
            'components' => ['nullable', 'string', 'max:2000'],
            'pilot_areas' => ['nullable', 'string', 'max:1000'],
            'year_implemented' => ['nullable', 'regex:/^\d{4}(?:\s*-\s*\d{4})?$/'],
            'status_remarks' => ['nullable', 'string', 'max:2000'],
            'resolution' => ['nullable', 'string', 'max:2000'],
            'guidelines' => ['nullable', 'string', 'max:2000'],
            'program_manual_outline' => ['nullable', 'string', 'max:5000'],
            'information_systems_developed' => ['nullable', 'string', 'max:2000'],
            'session_guide_key_topics' => ['nullable', 'string', 'max:5000'],
            'training_manual_outline' => ['nullable', 'string', 'max:5000'],
        ]);

        $title = trim((string) $request->input('social_technology'));
        $item = SocialTechnologyTitle::find($id);
        if (!$item) {
            if ($request->ajax()) {
                return response()->json(['message' => 'Title not found.'], 404);
            }
            return redirect()->route('STDashboard')->with('error', 'Title not found.');
        }

        Log::info('SocialTechnology update request', [
            'id' => $id,
            'input' => $request->all(),
            'user' => Auth::check() ? Auth::user()->id ?? Auth::user()->user_id : null,
        ]);

        $exists = SocialTechnologyTitle::where('social_technology', $title)->where('id', '!=', $id)->exists();
        if ($exists) {
            if ($request->ajax()) {
                return response()->json(['message' => 'A social technology with that value already exists.'], 422);
            }
            return redirect()->route('STDashboard')->with('error', 'A social technology with that value already exists.');
        }

        $data = $request->only([
            'sector',
            'laws_and_issuances',
            'social_technology',
            'description',
            'objectives',
            'components',
            'pilot_areas',
            'year_implemented',
            'status_remarks',
            'resolution',
            'guidelines',
            'program_manual_outline',
            'information_systems_developed',
            'session_guide_key_topics',
            'training_manual_outline',
        ]);
        $data['social_technology'] = $title;
        $data['updatedby'] = Auth::check() ? Auth::user()->name : null;

        $item->fill($data);
        $saved = $item->save();
        Log::info('SocialTechnology save result', ['id' => $id, 'saved' => $saved]);

        if ($request->ajax()) {
            return response()->json(['message' => 'Updated.']);
        }

        return redirect()->route('STDashboard')->with('status', 'Title updated.');
    }

    public function destroy(Request $request, $id)
    {
        $item = SocialTechnologyTitle::find($id);
        if (!$item) {
            if ($request->ajax()) {
                return response()->json(['message' => 'Title not found.'], 404);
            }
            return redirect()->route('STDashboard')->with('error', 'Title not found.');
        }

        $item->delete();

        if ($request->ajax()) {
            return response()->json(['message' => 'Deleted.']);
        }

        return redirect()->route('STDashboard')->with('status', 'Title deleted.');
    }
}
