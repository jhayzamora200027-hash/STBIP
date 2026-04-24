<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use App\Services\MigrationWriter;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;

class TableController extends Controller
{
    

    public function index()
    {
        $tables = [];
        
        $tableNames = DB::select('SHOW TABLES');
        $dbName = 'Tables_in_' . env('DB_DATABASE');

        foreach ($tableNames as $tableName) {
            $name = $tableName->$dbName;
            // count using query builder safely
            $count = DB::table($name)->count();

            $tables[] = (object) [
                'name' => $name,
                'count' => $count
            ];
        }
        
        return view('admin.sysadminfolder.tables', ['tables' => $tables]);
    }
    
    public function getColumns($tableName)
    {
        // Validate table name and use schema builder to get columns
        if (!$this->isValidIdentifier($tableName) || !Schema::hasTable($tableName)) {
            return response()->json([], 400);
        }
        $columns = Schema::getColumnListing($tableName);
        return response()->json($columns);
    }
    
    public function create(Request $request)
    {
        return $this->createTableWithMigration($request);
    }

    public function createTableWithMigration(Request $request)
    {
        try {
            $table = $request->input('table');
            if (!$this->isValidIdentifier($table)) {
                return response()->json(['success' => false, 'message' => 'Invalid table name']);
            }
            // Create table using Schema facade for safety
            if (!Schema::hasTable($table)) {
                Schema::create($table, function (\Illuminate\Database\Schema\Blueprint $t) {
                    $t->bigIncrements('id');
                    $t->timestamps();
                });
            }

            $writer = new MigrationWriter();
            $up = "Schema::create('$table', function (Blueprint $table) {\n    \$table->bigIncrements('id');\n    \$table->timestamps();\n});";
            $down = "Schema::dropIfExists('$table');";
                $migrationFile = $writer->createMigration('create_' . $table . '_table', $up, $down);
                $pattern = '%_' . $table . '_%';
                $maxBatch = DB::table('migrations')->where('migration', 'like', $pattern)->max('batch');
                $batch = $maxBatch ? $maxBatch + 1 : 1;
                DB::table('migrations')->insert([
                    'migration' => pathinfo($migrationFile, PATHINFO_FILENAME),
                    'batch' => $batch,
                    'createdby' => Auth::user()->user_id,
                ]);

            return response()->json(['success' => true, 'message' => 'Table created and migration generated']);
        } catch (\Exception $e) {
            return response()->json(['success' => false, 'message' => $e->getMessage()]);
        }
    }
    public function delete(Request $request)
    {
        return $this->deleteTableWithMigration($request);
    }
    public function deleteTableWithMigration(Request $request)
    {
        try {
            $table = $request->input('table');
            $table = $request->input('table');
            if (!$this->isValidIdentifier($table)) {
                return response()->json(['success' => false, 'message' => 'Invalid table name']);
            }
            if (Schema::hasTable($table)) {
                Schema::dropIfExists($table);
            }

            $writer = new MigrationWriter();
            $up = "Schema::dropIfExists('$table');";
            $down = "// Table deletion cannot be reversed automatically.";
                $migrationFile = $writer->createMigration('drop_' . $table . '_table', $up, $down);
                $pattern = '%_' . $table . '_%';
                $maxBatch = DB::table('migrations')->where('migration', 'like', $pattern)->max('batch');
                $batch = $maxBatch ? $maxBatch + 1 : 1;
                DB::table('migrations')->insert([
                    'migration' => pathinfo($migrationFile, PATHINFO_FILENAME),
                    'batch' => $batch
                ]);

            return response()->json(['success' => true, 'message' => 'Table deleted and migration generated']);
        } catch (\Exception $e) {
            return response()->json(['success' => false, 'message' => $e->getMessage()]);
        }
    }
    
    public function addColumnWithMigration(Request $request)
    {
        try {
            $table = $request->input('table');
            $column = $request->input('column');
            $type = $request->input('type');
            $nullableInput = $request->input('nullable');
            $nullable = '';
            if ($nullableInput === 'Nullable') {
                $nullable = 'NULL';
            } elseif ($nullableInput === 'Not Nullable') {
                $nullable = 'NOT NULL';
            } else {
                $nullable = $nullableInput; 
            }
            $table = $request->input('table');
            $column = $request->input('column');
            $type = $request->input('type');
            if (!$this->isValidIdentifier($table) || !$this->isValidIdentifier($column) || !$this->isAllowedType($type)) {
                return response()->json(['success' => false, 'message' => 'Invalid table/column/type']);
            }
            // Use schema builder to add column when possible (best-effort)
            Schema::table($table, function (\Illuminate\Database\Schema\Blueprint $t) use ($column, $type, $nullable) {
                // Basic mapping handled by existing helper
                $laravelType = 'string';
                // Fallback: perform raw alter only if absolutely necessary
            });
            // Fallback raw statement if schema builder couldn't be used for provided type
            $sql = "ALTER TABLE `{$table}` ADD COLUMN `{$column}` {$type} {$nullable}";
            DB::statement($sql);

            $writer = new MigrationWriter();
            $laravelType = $this->mapSqlTypeToLaravel($type);
            $nullableFlag = ($nullable === 'NULL') ? '->nullable()' : '';
            $up = "Schema::table('$table', function (Blueprint $table) {\n    \$table->{$laravelType}('$column'){$nullableFlag};\n});";
            $down = "Schema::table('$table', function (Blueprint $table) {\n    \$table->dropColumn('$column');\n});";
            $migrationFile = $writer->createMigration('add_' . $column . '_to_' . $table . '_table', $up, $down);
            $pattern = '%_' . $table . '_%';
            $maxBatch = DB::table('migrations')->where('migration', 'like', $pattern)->max('batch');
            $batch = $maxBatch ? $maxBatch + 1 : 1;
            DB::table('migrations')->insert([
                'migration' => pathinfo($migrationFile, PATHINFO_FILENAME),
                'batch' => $batch
            ]);

            return response()->json(['success' => true, 'message' => 'Column added and migration generated']);
        } catch (\Exception $e) {
            return response()->json(['success' => false, 'message' => $e->getMessage()]);
        }
    }

    private function mapSqlTypeToLaravel($type)
    {
        $type = strtoupper($type);
        if (strpos($type, 'VARCHAR') !== false) return 'string';
        if ($type === 'INT') return 'integer';
        if ($type === 'BIGINT') return 'bigInteger';
        if ($type === 'TEXT') return 'text';
        if ($type === 'BOOLEAN') return 'boolean';
        if ($type === 'DATE') return 'date';
        if ($type === 'DATETIME') return 'dateTime';
        if (strpos($type, 'DECIMAL') !== false) return 'decimal';
        return 'string'; 
    }

    private function isValidIdentifier($name)
    {
        return is_string($name) && preg_match('/^[A-Za-z_][A-Za-z0-9_]*$/', $name);
    }

    private function isAllowedType($type)
    {
        if (!is_string($type)) return false;
        $t = strtoupper(trim($type));
        // allow basic types with optional lengths/precision
        return (bool) preg_match('/^(INT|INTEGER|BIGINT|TEXT|DATE|DATETIME|TIMESTAMP|BOOLEAN|TINYINT|SMALLINT|MEDIUMINT|VARCHAR\(\d+\)|CHAR\(\d+\)|DECIMAL\(\d+,\d+\))$/i', $t);
    }

    public function deleteColumnWithMigration(Request $request)
    {
        try {
            $table = $request->input('table');
            $column = $request->input('column');

            $columns = DB::getSchemaBuilder()->getColumnListing($table);
            if (!in_array($column, $columns)) {
                return response()->json(['success' => false, 'message' => "Column '$column' does not exist in table '$table'."]);
            }

            $table = $request->input('table');
            $column = $request->input('column');
            if (!$this->isValidIdentifier($table) || !$this->isValidIdentifier($column)) {
                return response()->json(['success' => false, 'message' => 'Invalid table/column']);
            }
            if (!in_array($column, Schema::getColumnListing($table))) {
                return response()->json(['success' => false, 'message' => "Column '{$column}' does not exist in table '{$table}'."]);
            }
            $sql = "ALTER TABLE `{$table}` DROP COLUMN `{$column}`";
            DB::statement($sql);

            $writer = new MigrationWriter();
            $up = "Schema::table('$table', function (Blueprint $table) {\n    \$table->dropColumn('$column');\n});";
            $down = "// Column deletion cannot be reversed automatically.";
            $migrationFile = $writer->createMigration('drop_' . $column . '_from_' . $table . '_table', $up, $down);
            $pattern = '%_' . $table . '_%';
            $maxBatch = DB::table('migrations')->where('migration', 'like', $pattern)->max('batch');
            $batch = $maxBatch ? $maxBatch + 1 : 1;
            DB::table('migrations')->insert([
                'migration' => pathinfo($migrationFile, PATHINFO_FILENAME),
                'batch' => $batch
            ]);

            return response()->json(['success' => true, 'message' => 'Column deleted and migration generated']);
        } catch (\Exception $e) {
            Log::error('Error deleting column', [
                'table' => $table ?? null,
                'column' => $column ?? null,
                'error' => $e->getMessage(),
            ]);
            return response()->json(['success' => false, 'message' => $e->getMessage()]);
        }
    }
}
