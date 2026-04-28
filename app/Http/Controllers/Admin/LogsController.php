<?php
namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class LogsController extends Controller
{
   
    public function index(Request $request)
    {
        $module = $request->query('module');
        $perPage = 50;

        $results = collect();
        $gather = function (string $table, string $moduleName, array $mapping = []) use (&$results) {
            if (!DB::getSchemaBuilder()->hasTable($table)) return;
            $cols = DB::getSchemaBuilder()->getColumnListing($table);

            $actionCol = $mapping['action'] ?? (in_array('action', $cols) ? 'action' : (in_array('actionlogs', $cols) ? 'actionlogs' : null));
            $userCol = $mapping['user'] ?? (in_array('user_id', $cols) ? 'user_id' : (in_array('updated_by', $cols) ? 'updated_by' : (in_array('createdby', $cols) ? 'createdby' : null)));
            $timeCol = $mapping['time'] ?? (in_array('created_at', $cols) ? 'created_at' : (in_array('updated_at', $cols) ? 'updated_at' : null));
            $detailsCol = $mapping['details'] ?? (in_array('details', $cols) ? 'details' : (in_array('update_row', $cols) ? 'update_row' : (in_array('excelname', $cols) ? 'excelname' : (in_array('docselected', $cols) ? 'docselected' : null))));

            $q = DB::table($table);
            $q->selectRaw('? as module', [$moduleName]);

            $quote = function ($col) use ($cols) {
                if (!is_string($col)) return null;
                // only allow quoting actual columns from the table
                if (!in_array($col, $cols, true)) return null;
                $clean = preg_replace('/[^A-Za-z0-9_]/', '', $col);
                if ($clean === $col && preg_match('/^[A-Za-z_][A-Za-z0-9_]*$/', $col)) {
                    return "`{$col}`";
                }
                return null;
            };

            $selects = [];
            $qi = $quote('id');
            if (in_array('id', $cols) && $qi) {
                $selects[] = DB::raw("{$qi} as id");
            } else {
                $selects[] = DB::raw('NULL as id');
            }
            if ($actionCol) {
                $qc = $quote($actionCol);
                if ($qc) $selects[] = DB::raw("{$qc} as action");
            }
            if ($userCol) {
                $qu = $quote($userCol);
                if ($qu) $selects[] = DB::raw("{$qu} as user_id");
            }
            if ($timeCol) {
                $qt = $quote($timeCol);
                if ($qt) $selects[] = DB::raw("{$qt} as created_at");
            }
            if ($detailsCol) {
                $qd = $quote($detailsCol);
                if ($qd) {
                    if ($detailsCol === 'excelname') {
                        $selects[] = DB::raw("CONCAT('excel:', {$qd}) as details");
                    } else {
                        $selects[] = DB::raw("{$qd} as details");
                    }
                }
            }

            if (!empty($selects)) {
                $q->addSelect($selects);
            }

            $results = $results->concat($q->get());
        };

        if (!$module || $module === 'master_data') {
            $gather('region_item_histories', 'master_data', ['action' => 'action', 'user' => 'updated_by', 'details' => 'update_row', 'time' => 'created_at']);
        }

        if (!$module || $module === 'file_uploads') {
            $gather('uploadlogs', 'file_uploads', ['action' => 'action', 'user' => 'createdby', 'details' => 'excelname']);
        }


            if (!$module || $module === 'sector_utilities') {
                $gather('child_docno_histories', 'sector_utilities', ['user' => 'created_by', 'details' => 'notes', 'time' => 'created_at']);
                $gather('sector_utilities_logs', 'sector_utilities', ['action' => 'action', 'user' => 'user', 'details' => 'details', 'time' => 'created_at']);
            }

        if (!$module || $module === 'social_titles') {
            $gather('selectdocslogs', 'social_titles', ['action' => 'actionlogs', 'user' => 'createdby', 'details' => 'docselected']);
            $gather('social_technology_logs', 'social_titles', ['action' => 'action', 'user' => 'performed_by', 'details' => 'details', 'time' => 'created_at']);
        }

        if (!$module || $module === 'user_management') {
            $gather('userlogs', 'user_management', ['user' => 'performed_by', 'details' => 'meta']);
        }

        if (!$module || $module === 'user_approval') {
            if (DB::getSchemaBuilder()->hasTable('approval_histories')) {
                $q = DB::table('approval_histories')->leftJoin('users', 'approval_histories.user_id', '=', 'users.id');
                $q->selectRaw('? as module', ['user_approval']);
                $q->addSelect([
                    DB::raw('approval_histories.id as id'),
                    DB::raw('approval_histories.action as action'),
                    // prefer reviewer name stored in table, then users.name, otherwise user_id
                    DB::raw("COALESCE(approval_histories.reviewed_by_name, users.name, approval_histories.user_id) as user_id"),
                    // details: show applicant email and include rejection reason when present
                    DB::raw("CONCAT(approval_histories.applicant_email, IF(approval_histories.rejection_reason IS NOT NULL AND approval_histories.rejection_reason <> '', CONCAT(' -- ', approval_histories.rejection_reason), '')) as details"),
                    DB::raw('approval_histories.created_at as created_at'),
                ]);
                $results = $results->concat($q->get());
            }
        }

        $sorted = $results->sortByDesc(function ($r) { return $r->created_at ?? ($r->updated_at ?? null); })->values();

        $requiredTables = [
            'child_docno_histories',
            'uploadlogs',
            'selectdocslogs',
            'social_technology_logs',
            'userlogs',
        ];
        $missing = [];
        foreach ($requiredTables as $t) {
            if (!DB::getSchemaBuilder()->hasTable($t)) $missing[] = $t;
        }

        $page = max(1, (int) $request->query('page', 1));
        $total = $sorted->count();
        $slice = $sorted->forPage($page, $perPage);

        return view('admin.logs.index', [
            'logs' => $slice,
            'module' => $module,
            'page' => $page,
            'perPage' => $perPage,
            'total' => $total,
            'missingTables' => $missing,
        ]);
    }
}
