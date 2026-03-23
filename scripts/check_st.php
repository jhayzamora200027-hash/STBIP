<?php
require __DIR__ . '/../vendor/autoload.php';
$app = require_once __DIR__ . '/../bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();
echo "DB connection default: " . config('database.default') . PHP_EOL;
echo "DB config host: " . config('database.connections.' . config('database.default') . '.host') . PHP_EOL;
echo "DB config database: " . config('database.connections.' . config('database.default') . '.database') . PHP_EOL;
$row = \Illuminate\Support\Facades\DB::table('social_technology_titles')->where('id', 176)->first();
echo "Row dump for id=176:\n";
var_export($row);
echo PHP_EOL;
