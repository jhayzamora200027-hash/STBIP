<?php

use App\Http\Controllers\MainReportController;
use Illuminate\Container\Container;
use Illuminate\Contracts\Console\Kernel as ConsoleKernel;
use Illuminate\Support\Facades\Facade;

require __DIR__ . '/../vendor/autoload.php';

$app = require __DIR__ . '/../bootstrap/app.php';
Container::setInstance($app);
Facade::setFacadeApplication($app);

/** @var ConsoleKernel $kernel */
$kernel = $app->make(ConsoleKernel::class);
$kernel->bootstrap();

$controller = new MainReportController();
$path = $controller->findLatestExcelPath();
echo "Excel path: {$path}\n";

if (!$path) {
    echo "No Excel found.\n";
    exit(0);
}

$parsed = $controller->getParsedData($path);
$data = $parsed['data'] ?? [];
$regionMap = $parsed['regionMap'] ?? [];

$regionArg = $argv[1] ?? null;

$regionsInData = array_values(array_unique(array_map(function($r){ return $r['region'] ?? '(none)'; }, $data)));

foreach ($regionsInData as $regionName) {
    if ($regionArg !== null && strtolower($regionArg) !== 'all' && $regionName !== $regionArg) {
        continue;
    }

    $rows = array_values(array_filter($data, function ($r) use ($regionName) {
        return ($r['region'] ?? null) === $regionName;
    }));

    echo "Region: {$regionName}\n";
    echo "Rows: " . count($rows) . "\n";

    $hdrs = $regionMap[$regionName]['headers'] ?? [];
    $adoptIdx = false;
    $repIdx = false;
    foreach ($hdrs as $i => $h) {
        $h = strtolower((string)$h);
        if ($adoptIdx === false && strpos($h, 'adopt') !== false) {
            $adoptIdx = $i;
        }
        if ($repIdx === false && strpos($h, 'replic') !== false) {
            if ($adoptIdx !== false && $i === $adoptIdx) {
                // skip combined adopted/replicated column when looking for dedicated replicated
            } else {
                $repIdx = $i;
            }
        }
    }

    echo "adoptIdx=" . var_export($adoptIdx,true) . " repIdx=" . var_export($repIdx,true) . "\n";

    $normalizeBool = function($v) {
        if (is_bool($v)) return $v;
        $s = strtolower(trim((string)$v));
        return $s === 'true' || $s === '1';
    };
    $normalizeCount = function($v) use ($normalizeBool) {
        if (is_numeric($v)) return (int)$v;
        if ($normalizeBool($v)) return 1;
        $s = trim((string)$v);
        return $s !== '' ? 1 : 0;
    };

    $sumAdopt = 0;
    $sumRep = 0;

    foreach ($rows as $idx => $r) {
        if ($adoptIdx !== false && isset($r['row'][$adoptIdx])) {
            $sumAdopt += $normalizeCount($r['row'][$adoptIdx]);
        }
        if ($repIdx !== false && isset($r['row'][$repIdx])) {
            $sumRep += $normalizeCount($r['row'][$repIdx]);
        }
    }

    echo "Computed adopted total: {$sumAdopt}\n";
    echo "Computed replicated total: {$sumRep}\n";
    echo str_repeat('-',40) . "\n";
}
