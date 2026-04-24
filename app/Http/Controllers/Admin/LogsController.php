<?php
namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class LogsController extends Controller
{
    /**
     * Show aggregated logs filtered by module
     */
    public function index(Request $request)
    {
        $module = $request->query('module');
        $perPage = 50;

        $results = collect();
        // Helper: safely select available columns from a table and map to common fields
        $gather = function (string $table, string $moduleName, array $mapping = []) use (&$results) {
            if (!DB::getSchemaBuilder()->hasTable($table)) return;
            $cols = DB::getSchemaBuilder()->getColumnListing($table);

            // determine which columns to use
            $actionCol = $mapping['action'] ?? (in_array('action', $cols) ? 'action' : (in_array('actionlogs', $cols) ? 'actionlogs' : null));
            $userCol = $mapping['user'] ?? (in_array('user_id', $cols) ? 'user_id' : (in_array('updated_by', $cols) ? 'updated_by' : (in_array('createdby', $cols) ? 'createdby' : null)));
            $timeCol = $mapping['time'] ?? (in_array('created_at', $cols) ? 'created_at' : (in_array('updated_at', $cols) ? 'updated_at' : null));
            $detailsCol = $mapping['details'] ?? (in_array('details', $cols) ? 'details' : (in_array('update_row', $cols) ? 'update_row' : (in_array('excelname', $cols) ? 'excelname' : (in_array('docselected', $cols) ? 'docselected' : null))));

            // Build a safe query: bind the module name and use column names detected from schema
            $q = DB::table($table);
            // Bind module name as a value to avoid direct interpolation
            $q->selectRaw('? as module', [$moduleName]);

            // Helper to safely quote column identifiers (only allow safe identifier chars)
            $quote = function ($col) {
                if (!is_string($col)) return null;
                $clean = preg_replace('/[^A-Za-z0-9_]/', '', $col);
                if ($clean === $col && preg_match('/^[A-Za-z_][A-Za-z0-9_]*$/', $col)) {
                    return "`{$col}`";
                }
                return null;
            };

            $selects = [];
            if (in_array('id', $cols)) $selects[] = '`id`';
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

            // run query and merge
            $results = $results->concat($q->get());
        };

        // Master Data
        if (!$module || $module === 'master_data') {
            $gather('region_item_histories', 'master_data', ['action' => 'action', 'user' => 'updated_by', 'details' => 'update_row', 'time' => 'created_at']);
        }

        // Sector Utilities / Uploads
        if (!$module || $module === 'sector_utilities') {
            $gather('uploadlogs', 'sector_utilities', ['action' => 'action', 'user' => 'createdby', 'details' => 'excelname']);
        }

        // Social Technology Titles
        if (!$module || $module === 'social_titles') {
            $gather('selectdocslogs', 'social_titles', ['action' => 'actionlogs', 'user' => 'createdby', 'details' => 'docselected']);
        }

        // User Management
        if (!$module || $module === 'user_management') {
            $gather('userlogs', 'user_management');
        }

        // User Approval
        if (!$module || $module === 'user_approval') {
            $gather('approval_histories', 'user_approval', ['details' => 'applicant_email']);
        }

        // combine, sort by created_at desc
        $sorted = $results->sortByDesc(function ($r) { return $r->created_at ?? ($r->updated_at ?? null); })->values();

        // simple manual pagination
        $page = max(1, (int) $request->query('page', 1));
        $total = $sorted->count();
        $slice = $sorted->forPage($page, $perPage);

        return view('admin.logs.index', [
            'logs' => $slice,
            'module' => $module,
            'page' => $page,
            'perPage' => $perPage,
            'total' => $total,
        ]);
    }
}
