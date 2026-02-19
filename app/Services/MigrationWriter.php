<?php

namespace App\Services;

use Illuminate\Support\Str;
use Illuminate\Filesystem\Filesystem;

class MigrationWriter
{
    protected $filesystem;
    protected $migrationPath;

    public function __construct()
    {
        $this->filesystem = new Filesystem();
        $this->migrationPath = base_path('database/migrations');
    }

    public function createMigration($name, $up, $down)
    {
        $timestamp = now()->format('Y_m_d_His');
        $fileName = $timestamp . '_' . Str::snake($name) . '.php';
        $filePath = $this->migrationPath . '/' . $fileName;

        $content = $this->buildMigrationContent($name, $up, $down);
        $this->filesystem->put($filePath, $content);
        return $fileName;
    }

    protected function buildMigrationContent($name, $up, $down)
    {
        // Ensure $up and $down are valid PHP code blocks
        $up = rtrim($up, ";\n") . ";"; // Ensure semicolon at end
        $down = rtrim($down, ";\n") . ";";

        // Normalize any Schema callback like
        //   function (Blueprint users)
        //   function (Illuminate\Database\Schema\Blueprint users)
        //   function (Illuminate\Database\Schema\Blueprint $users)
        // into: function (Blueprint $table)
        $pattern = '/function\s*\(\s*(?:[\\\\A-Za-z0-9_]+\\\\)?Blueprint\s+\$?\w+\s*\)/';
        $replacement = 'function (Blueprint $table)';

        $up = preg_replace($pattern, $replacement, $up);
        $down = preg_replace($pattern, $replacement, $down);

        return "<?php\n\nuse Illuminate\\Database\\Migrations\\Migration;\nuse Illuminate\\Database\\Schema\\Blueprint;\nuse Illuminate\\Support\\Facades\\Schema;\n\nreturn new class extends Migration {\n    public function up()\n    {\n        {$up}\n    }\n\n    public function down()\n    {\n        {$down}\n    }\n};\n";
    }
}
