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
        $action = $request->query('action');
        $fromDate = $request->query('from_date');
        $toDate = $request->query('to_date');
        $perPage = 50;

        $results = collect();
        $isSqlite = DB::connection()->getDriverName() === 'sqlite';

        $gather = function (string $table, string $moduleName, array $mapping = []) use (&$results, $isSqlite, $action) {
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
                $selects[] = DB::raw("`{$table}`.`id` as id");
            } else {
                $selects[] = DB::raw('NULL as id');
            }
            if ($actionCol) {
                // qualify action with the table name to avoid ambiguity when joined
                $selects[] = DB::raw("`{$table}`.`{$actionCol}` as action");
            }
            if ($userCol) {
                $qu = $quote($userCol);
                if ($qu) {
                    // For userlogs, prefer the user's name from the users table when available
                    if ($table === 'userlogs') {
                        // join users by the user column (which may be user_id or performed_by)
                        $q->leftJoin('users', 'users.id', '=', "{$table}.{$userCol}");
                        $fallback = "`{$table}`.`{$userCol}`";
                        $selects[] = DB::raw("COALESCE(users.name, {$fallback}) as user_id");
                    } else {
                        $selects[] = DB::raw("{$qu} as user_id");
                    }
                }
            }
            if ($timeCol) {
                // qualify time column with table name to avoid ambiguity
                $selects[] = DB::raw("`{$table}`.`{$timeCol}` as created_at");
            }
            if ($detailsCol) {
                if ($table === 'userlogs' && $moduleName === 'authentication') {
                    if ($isSqlite) {
                        $selects[] = DB::raw("strftime('%H:%M:%S', `{$table}`.`created_at`) as details");
                    } else {
                        $selects[] = DB::raw("DATE_FORMAT(`{$table}`.`created_at`, '%H:%i:%s') as details");
                    }
                } elseif ($detailsCol === 'excelname') {
                    if ($isSqlite) {
                        $selects[] = DB::raw("'excel:' || `{$table}`.`{$detailsCol}` as details");
                    } else {
                        $selects[] = DB::raw("CONCAT('excel:', `{$table}`.`{$detailsCol}`) as details");
                    }
                } else {
                    $selects[] = DB::raw("`{$table}`.`{$detailsCol}` as details");
                }
            }

            if (!empty($selects)) {
                $q->addSelect($selects);
            }

            // apply action filter when requested and the table has an action-like column
            if (!empty($action) && $actionCol) {
                $q->whereRaw("`{$table}`.`{$actionCol}` = ?", [$action]);
            } elseif ($table === 'userlogs' && $moduleName === 'authentication' && $actionCol) {
                $q->whereIn("{$table}.{$actionCol}", ['login', 'logout']);
            } elseif ($table === 'userlogs' && $moduleName === 'user_management' && $actionCol) {
                $q->whereNotIn("{$table}.{$actionCol}", ['login', 'logout']);
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

        // Authentication-specific view for login/logout entries
        if (!$module || $module === 'authentication') {
            $gather('userlogs', 'authentication', ['action' => 'action', 'user' => 'user_id', 'details' => 'meta', 'time' => 'created_at']);
        }

        if (!$module || $module === 'user_approval') {
            if (DB::getSchemaBuilder()->hasTable('approval_histories')) {
                $q = DB::table('approval_histories')->leftJoin('users', 'approval_histories.user_id', '=', 'users.id');
                $q->selectRaw('? as module', ['user_approval']);
                if ($isSqlite) {
                    $detailsExpr = "approval_histories.applicant_email || (CASE WHEN approval_histories.rejection_reason IS NOT NULL AND approval_histories.rejection_reason <> '' THEN ' -- ' || approval_histories.rejection_reason ELSE '' END) as details";
                } else {
                    $detailsExpr = "CONCAT(approval_histories.applicant_email, IF(approval_histories.rejection_reason IS NOT NULL AND approval_histories.rejection_reason <> '', CONCAT(' -- ', approval_histories.rejection_reason), '')) as details";
                }

                $q->addSelect([
                    DB::raw('approval_histories.id as id'),
                    DB::raw('approval_histories.action as action'),
                    // prefer reviewer name stored in table, then users.name, otherwise user_id
                    DB::raw("COALESCE(approval_histories.reviewed_by_name, users.name, approval_histories.user_id) as user_id"),
                    // details: show applicant email and include rejection reason when present
                    DB::raw($detailsExpr),
                    DB::raw('approval_histories.created_at as created_at'),
                ]);
                $results = $results->concat($q->get());
            }
        }

        $filtered = $results->filter(function ($row) use ($fromDate, $toDate) {
            $timestamp = $row->created_at ?? ($row->updated_at ?? null);
            if (!$timestamp) {
                return true;
            }

            $date = substr((string) $timestamp, 0, 10);

            if ($fromDate && $date < $fromDate) {
                return false;
            }

            if ($toDate && $date > $toDate) {
                return false;
            }

            return true;
        });

        $sorted = $filtered->sortByDesc(function ($r) { return $r->created_at ?? ($r->updated_at ?? null); })->values();

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
            'action' => $action,
            'fromDate' => $fromDate,
            'toDate' => $toDate,
            'page' => $page,
            'perPage' => $perPage,
            'total' => $total,
            'missingTables' => $missing,
        ]);
    }
}
