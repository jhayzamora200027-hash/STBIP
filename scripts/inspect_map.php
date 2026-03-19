<?php
require __DIR__ . '/../vendor/autoload.php';
use PhpOffice\PhpSpreadsheet\IOFactory;
$p = __DIR__ . '/../storage/app/excels/region_items_export_20260319_094434.xlsx';
if (!file_exists($p)) { echo "MISSING: {$p}\n"; exit(2); }
$sheet = IOFactory::load($p)->getActiveSheet();
$rows = $sheet->toArray(null, true, true, true);
$headerIndex = 1;
$headerRow = array_change_key_case(array_map('trim',(array)$rows[$headerIndex]), CASE_LOWER);
$map = [];
foreach ($headerRow as $col => $val) {
    $norm = strtolower($val);
    if (str_contains($norm,'region')) $map['region'] = $col;
    elseif (str_contains($norm,'title')) $map['title'] = $col;
    elseif (str_contains($norm,'status')) $map['status'] = $col;
    elseif (str_contains($norm,'province')) $map['province'] = $col;
    elseif (str_contains($norm,'municipality')) $map['municipality'] = $col;
    elseif (str_contains($norm,'adopt')) $map['adoption'] = $col;
    elseif (str_contains($norm,'expression')||str_contains($norm,'expr')) $map['with_expr'] = $col;
    elseif (str_contains($norm,'moa') && str_contains($norm,'year')) $map['year_of_moa'] = $col;
    elseif (str_contains($norm,'moa')) $map['with_moa'] = $col;
    elseif (str_contains($norm,'resolution')||str_contains($norm,'res')) {
        if (str_contains($norm,'year')) $map['year_of_resolution'] = $col; else $map['with_res'] = $col;
    } elseif (str_contains($norm,'aip')) $map['included_aip'] = $col;
}
if (!isset($map['title'])) {
    $maxCheck = min(5, count($rows));
    for ($i=1;$i<=$maxCheck;$i++){
        $candidate = array_change_key_case(array_map('trim',(array)$rows[$i]), CASE_LOWER);
        $foundTitle = false;
        foreach ($candidate as $c) if (is_string($c) && str_contains(strtolower($c),'title')) { $foundTitle=true; break; }
        if ($foundTitle){ $headerIndex=$i; $headerRow=$candidate; $map=[]; foreach ($headerRow as $col=>$val){ $norm=strtolower($val); if (str_contains($norm,'region')) $map['region']=$col; elseif (str_contains($norm,'title')) $map['title']=$col; elseif (str_contains($norm,'status')) $map['status']=$col; elseif (str_contains($norm,'province')) $map['province']=$col; elseif (str_contains($norm,'municipality')) $map['municipality']=$col; elseif (str_contains($norm,'adopt')) $map['adoption']=$col; elseif (str_contains($norm,'expression')||str_contains($norm,'expr')) $map['with_expr']=$col; elseif (str_contains($norm,'moa')&&str_contains($norm,'year')) $map['year_of_moa']=$col; elseif (str_contains($norm,'moa')) $map['with_moa']=$col; elseif (str_contains($norm,'resolution')||str_contains($norm,'res')) { if (str_contains($norm,'year')) $map['year_of_resolution']=$col; else $map['with_res']=$col; } elseif (str_contains($norm,'aip')) $map['included_aip']=$col; } break; }
    }
}
echo "Detected header row: {$headerIndex}\n";
echo "Column map: "; print_r($map);
$sample = array_slice($rows,$headerIndex,10);
foreach ($sample as $i=>$r){ $rowNum=$headerIndex+$i+1; echo "DATA ROW {$rowNum}: "; $region = isset($map['region'])?($r[$map['region']]??''):''; $title = isset($map['title'])?($r[$map['title']]??''):''; echo "region_raw='".trim((string)$region)."' | title_raw='".trim((string)$title)."'\n"; }
