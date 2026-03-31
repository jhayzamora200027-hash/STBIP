<?php
require __DIR__ . '/../vendor/autoload.php';
use PhpOffice\PhpSpreadsheet\IOFactory;
$p = __DIR__ . '/../storage/app/excels/region_items_export_20260319_094434.xlsx';
if (!file_exists($p)) { echo "MISSING: {$p}\n"; exit(2); }
$sheet = IOFactory::load($p)->getActiveSheet();
$rows = $sheet->toArray(null, true, true, true);
$headerIndex = 1;
$map = [];
$headerRow = array_map('trim',(array)$rows[$headerIndex]);
foreach ($headerRow as $col => $val) {
    $norm = strtolower($val);
    if (str_contains($norm,'region')) $map['region'] = strtoupper($col);
    elseif (str_contains($norm,'title')) $map['title'] = strtoupper($col);
    elseif (str_contains($norm,'province')) $map['province'] = strtoupper($col);
    elseif (str_contains($norm,'municipality')) $map['municipality'] = strtoupper($col);
}
if (!isset($map['title'])) {
    $maxCheck = min(5, count($rows));
    for ($i=1;$i<=$maxCheck;$i++){
        $candidate = array_map('trim',(array)$rows[$i]);
        $foundTitle = false;
        foreach ($candidate as $c) if (is_string($c) && str_contains(strtolower($c),'title')) { $foundTitle=true; break; }
        if ($foundTitle){ $headerIndex=$i; $headerRow=$candidate; $map=[]; foreach ($headerRow as $col=>$val){ $norm=strtolower($val); if (str_contains($norm,'region')) $map['region']=strtoupper($col); elseif (str_contains($norm,'title')) $map['title']=strtoupper($col); elseif (str_contains($norm,'province')) $map['province']=strtoupper($col); elseif (str_contains($norm,'municipality')) $map['municipality']=strtoupper($col); } break; }
    }
}
$totalRows = 0; $emptyTitle = []; $perRegion = []; $dupes = []; $seen = [];
$dataRows = array_slice($rows, $headerIndex);
foreach ($dataRows as $i => $r) {
    $sheetRow = $headerIndex + $i + 1;
    $totalRows++;
    $region = isset($map['region']) ? trim((string)($r[$map['region']] ?? '')) : '';
    $title = isset($map['title']) ? trim((string)($r[$map['title']] ?? '')) : '';
    $province = isset($map['province']) ? trim((string)($r[$map['province']] ?? '')) : '';
    $municipality = isset($map['municipality']) ? trim((string)($r[$map['municipality']] ?? '')) : '';
    $region = str_replace("\n", ' ', $region);
    $title = str_replace("\n", ' ', $title);
    $province = str_replace("\n", ' ', $province);
    $municipality = str_replace("\n", ' ', $municipality);

    if ($title === '') {
        $emptyTitle[] = $sheetRow;
    }
    $rn = $region !== '' ? $region : '(EMPTY)';
    if (!isset($perRegion[$rn])) $perRegion[$rn] = 0;
    $perRegion[$rn]++;

    $key = strtolower($region) . '||' . strtolower($title) . '||' . strtolower($province) . '||' . strtolower($municipality);
    if (isset($seen[$key])) {
        $seen[$key]['count']++;
        $seen[$key]['rows'][] = $sheetRow;
    } else {
        $seen[$key] = ['count'=>1, 'rows'=>[$sheetRow], 'region'=>$region, 'title'=>$title, 'province'=>$province, 'municipality'=>$municipality];
    }
}
foreach ($seen as $k => $v) if ($v['count'] > 1) $dupes[$k] = $v;
echo "File: {$p}\n";
echo "Detected header row: {$headerIndex}\n";
echo "Total data rows (after header): {$totalRows}\n";
echo "Rows with empty title: " . count($emptyTitle) . "\n";
if ($emptyTitle) { echo "First empty title rows: "; echo implode(', ', array_slice($emptyTitle,0,20)); echo "\n"; }

echo "Per-region counts (top 30):\n";
arsort($perRegion);
$ctr=0; foreach ($perRegion as $region=>$c) { echo "  {$region}: {$c}\n"; $ctr++; if ($ctr>=30) break; }

echo "Duplicate identity rows inside file: " . count($dupes) . " entries\n";
$ctr=0; foreach ($dupes as $k=>$v) { echo "- Duplicate #".($ctr+1)." count={$v['count']} rows=".implode(',', $v['rows'])." region='{$v['region']}' title='{$v['title']}'\n"; $ctr++; if ($ctr>=20) break; }

echo "\nRegions with empty-title rows (first 20):\n";
$regionsWithEmpty = [];
$dataRows = array_slice($rows, $headerIndex);
foreach ($dataRows as $i => $r) {
    $sheetRow = $headerIndex + $i + 1;
    $region = isset($map['region']) ? trim((string)($r[$map['region']] ?? '')) : '';
    $title = isset($map['title']) ? trim((string)($r[$map['title']] ?? '')) : '';
    if ($title === '') {
        $rn = $region !== '' ? $region : '(EMPTY)';
        if (!isset($regionsWithEmpty[$rn])) $regionsWithEmpty[$rn] = 0;
        $regionsWithEmpty[$rn]++;
    }
}
arsort($regionsWithEmpty);
$ctr=0; foreach ($regionsWithEmpty as $rg=>$c) { echo "  {$rg}: {$c}\n"; $ctr++; if ($ctr>=20) break; }

exit(0);
