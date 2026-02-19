<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use App\Services\MigrationWriter;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;

class TableController extends Controller
{
    

    // Display admin dashboard with all database tables
    public function index()
    {
        $tables = [];
        
        // Get all table names from database
        $tableNames = DB::select('SHOW TABLES');
        $dbName = 'Tables_in_' . env('DB_DATABASE');
        
        foreach ($tableNames as $tableName) {
            $name = $tableName->$dbName;
            $count = DB::table($name)->count();
            
            $tables[] = (object) [
                'name' => $name,
                'count' => $count
            ];
        }
        
        return view('admin.sysadminfolder.tables', ['tables' => $tables]);
    }
    
    // Get all columns information for a specific table
    public function getColumns($tableName)
    {
        $columns = DB::select("DESCRIBE {$tableName}");
        return response()->json($columns);
    }
    
    // Create a new table in the database and generate migration
    public function create(Request $request)
    {
        return $this->createTableWithMigration($request);
    }

    // Actual logic for creating a table and migration
    public function createTableWithMigration(Request $request)
    {
        try {
            $table = $request->input('table');
            $sql = "CREATE TABLE {$table} (
                id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
                created_at TIMESTAMP NULL,
                updated_at TIMESTAMP NULL
            )";
            DB::statement($sql);

            // Generate migration file
            $writer = new MigrationWriter();
            $up = "Schema::create('$table', function (Blueprint $table) {\n    \$table->bigIncrements('id');\n    \$table->timestamps();\n});";
            $down = "Schema::dropIfExists('$table');";
                $migrationFile = $writer->createMigration('create_' . $table . '_table', $up, $down);
                $maxBatch = DB::table('migrations')->where('migration', 'like', '%_' . $table . '_%')->max('batch');
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
    // Route handler for deleting a table
    public function delete(Request $request)
    {
        return $this->deleteTableWithMigration($request);
    }
    // Delete an entire table and generate migration
    public function deleteTableWithMigration(Request $request)
    {
        try {
            $table = $request->input('table');
            $sql = "DROP TABLE {$table}";
            DB::statement($sql);

            // Generate migration file
            $writer = new MigrationWriter();
            $up = "Schema::dropIfExists('$table');";
            $down = "// Table deletion cannot be reversed automatically.";
                $migrationFile = $writer->createMigration('drop_' . $table . '_table', $up, $down);
                $maxBatch = DB::table('migrations')->where('migration', 'like', '%_' . $table . '_%')->max('batch');
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
    
    // Add a new column and generate migration
    public function addColumnWithMigration(Request $request)
    {
        try {
            $table = $request->input('table');
            $column = $request->input('column');
            $type = $request->input('type');
            $nullableInput = $request->input('nullable');
            // Map frontend value to SQL
            $nullable = '';
            if ($nullableInput === 'Nullable') {
                $nullable = 'NULL';
            } elseif ($nullableInput === 'Not Nullable') {
                $nullable = 'NOT NULL';
            } else {
                $nullable = $nullableInput; // fallback for direct SQL values
            }
            $sql = "ALTER TABLE {$table} ADD COLUMN {$column} {$type} {$nullable}";
            DB::statement($sql);

            // Generate migration file
            $writer = new MigrationWriter();
            // Map SQL type to Laravel migration type
            $laravelType = $this->mapSqlTypeToLaravel($type);
            $nullableFlag = ($nullable === 'NULL') ? '->nullable()' : '';
            $up = "Schema::table('$table', function (Blueprint $table) {\n    \$table->{$laravelType}('$column'){$nullableFlag};\n});";
            $down = "Schema::table('$table', function (Blueprint $table) {\n    \$table->dropColumn('$column');\n});";
            $migrationFile = $writer->createMigration('add_' . $column . '_to_' . $table . '_table', $up, $down);
            $maxBatch = DB::table('migrations')->where('migration', 'like', '%_' . $table . '_%')->max('batch');
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

    // Helper to map SQL type to Laravel migration type
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
        return 'string'; // default
    }

    // Delete a column and generate migration
    public function deleteColumnWithMigration(Request $request)
    {
        try {
            $table = $request->input('table');
            $column = $request->input('column');

            // Check if column exists
            $columns = DB::getSchemaBuilder()->getColumnListing($table);
            if (!in_array($column, $columns)) {
                return response()->json(['success' => false, 'message' => "Column '$column' does not exist in table '$table'."]);
            }

            // Escape table and column names with backticks
            $sql = "ALTER TABLE `{$table}` DROP COLUMN `{$column}`";
            DB::statement($sql);

            // Generate migration file
            $writer = new MigrationWriter();
            $up = "Schema::table('$table', function (Blueprint $table) {\n    \$table->dropColumn('$column');\n});";
            $down = "// Column deletion cannot be reversed automatically.";
            $migrationFile = $writer->createMigration('drop_' . $column . '_from_' . $table . '_table', $up, $down);
            $maxBatch = DB::table('migrations')->where('migration', 'like', '%_' . $table . '_%')->max('batch');
            $batch = $maxBatch ? $maxBatch + 1 : 1;
            DB::table('migrations')->insert([
                'migration' => pathinfo($migrationFile, PATHINFO_FILENAME),
                'batch' => $batch
            ]);

            return response()->json(['success' => true, 'message' => 'Column deleted and migration generated']);
        } catch (\Exception $e) {
            // Add more detailed error reporting
            Log::error('Error deleting column', [
                'table' => $table ?? null,
                'column' => $column ?? null,
                'error' => $e->getMessage(),
            ]);
            return response()->json(['success' => false, 'message' => $e->getMessage()]);
        }
    }
}
