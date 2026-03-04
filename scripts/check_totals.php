<?php

use App\Http\Controllers\MainReportController;
use Illuminate\Container\Container;
use Illuminate\Contracts\Console\Kernel as ConsoleKernel;
use Illuminate\Support\Facades\Facade;

require __DIR__ . '/../vendor/autoload.php';

$app = require __DIR__ . '/../bootstrap/app.php';
Container::setInstance($app);
Facade::setFacadeApplication($app);

// fully bootstrap Laravel so Eloquent models (Selectdocslogs, etc.)
// can resolve their database connection.
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
$headers = $parsed['headers'] ?? [];
$regionMap = $parsed['regionMap'] ?? [];

// if a region name is provided, limit to that; otherwise iterate over all
// regions and print their totals so we can see which one matches the
// dashboard output (useful when sheet names contain extra text/spacing).
$regionArg = $argv[1] ?? null;

$regionsInData = array_values(array_unique(array_map(function($r){ return $r['region'] ?? '(none)'; }, $data)));

if ($regionArg === null || strtolower($regionArg) === 'all') {
	echo "Available regions in data:" . PHP_EOL;
	foreach ($regionsInData as $rname) {
		echo "  - {$rname}" . PHP_EOL;
	}
	echo PHP_EOL;
} 

foreach ($regionsInData as $regionName) {
	if ($regionArg !== null && strtolower($regionArg) !== 'all' && $regionName !== $regionArg) {
		continue;
	}

	$rows = array_values(array_filter($data, function ($r) use ($regionName) {
		return ($r['region'] ?? null) === $regionName;
	}));

	echo "Region: {$regionName}\n";
	echo "Rows: " . count($rows) . "\n";

	// locate ongoing/dissolved columns using this region's own headers
	$hdrs = $regionMap[$regionName]['headers'] ?? $headers;
	$idxOng = null;
	$idxDis = null;
	foreach ($hdrs as $i => $h) {
		if ($idxOng === null && stripos($h, 'ongoing') !== false) {
			$idxOng = $i;
		}
		if ($idxDis === null && (stripos($h, 'dissolved') !== false || stripos($h, 'inactive') !== false)) {
			$idxDis = $i;
		}
	}

	echo "idxOng=" . var_export($idxOng, true) . " idxDis=" . var_export($idxDis, true) . "\n";
	if ($regionArg !== null && $regionName === $regionArg) {
		echo "Headers for {$regionName}:\n";
		foreach ($hdrs as $i => $h) {
			echo "  [{$i}] {$h}\n";
		}
	}

// mirror MainReportController's strict TRUE-only rule: only explicit TRUE
// values (boolean true or the text "TRUE") are counted as checked.
$statusCellIsTrue = function ($v) {
	if (is_bool($v)) return $v;
	if ($v === null) return false;
	$s = strtolower(trim((string) $v));
	return $s === 'true';
};

	$sumOng = 0;
	$sumDis = 0;
	$byStatus = [];
	$trueRowsOng = [];
	$trueRowsDis = [];

	foreach ($rows as $idx => $r) {
	$st = strtolower($r['status'] ?? '');

	$ongoCnt = 0;
	$disCnt = 0;

	if ($idxOng !== null && isset($r['row'][$idxOng])) {
		$val = $r['row'][$idxOng];
		if (is_numeric($val)) {
			$ongoCnt = max(0, (int) $val);
		} elseif ($statusCellIsTrue($val)) {
			$ongoCnt = 1;
		}
	}
	if ($idxDis !== null && isset($r['row'][$idxDis])) {
		$val = $r['row'][$idxDis];
		if (is_numeric($val)) {
			$disCnt = max(0, (int) $val);
		} elseif ($statusCellIsTrue($val)) {
			$disCnt = 1;
		}
	}

	// no fallback from free-text status: we want the debug output
	// to match the card rule "count only the true" in the sheet.

	$sumOng += $ongoCnt;
	$sumDis += $disCnt;
	if ($ongoCnt > 0) {
		$trueRowsOng[] = [$idx, $r['row'][$idxOng] ?? null];
	}
	if ($disCnt > 0) {
		$trueRowsDis[] = [$idx, $r['row'][$idxDis] ?? null];
	}

	$key = $st !== '' ? $st : '(blank)';
	if (!isset($byStatus[$key])) $byStatus[$key] = 0;
	$byStatus[$key]++;
	}

	echo "Computed ongoing total: {$sumOng}\n";
	echo "Computed dissolved total: {$sumDis}\n";
	echo "Status distribution:" . PHP_EOL;
	foreach ($byStatus as $st => $cnt) {
		echo "  {$st}: {$cnt}\n";
	}
	if ($regionArg !== null && $regionName === $regionArg) {
		echo "Rows counted as TRUE in Ongoing for {$regionName}:\n";
		foreach ($trueRowsOng as [$i,$v]) {
			echo "  data-index {$i}: value=" . var_export($v, true) . "\n";
		}
		echo "Rows counted as TRUE in Dissolved for {$regionName}:\n";
		foreach ($trueRowsDis as [$i,$v]) {
			echo "  data-index {$i}: value=" . var_export($v, true) . "\n";
		}
	}
	echo str_repeat('-', 40) . PHP_EOL;
}

