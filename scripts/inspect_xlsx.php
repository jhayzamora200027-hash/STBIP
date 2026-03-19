<?php
require __DIR__ . '/../vendor/autoload.php';
use PhpOffice\PhpSpreadsheet\IOFactory;
$p = __DIR__ . '/../storage/app/excels/region_items_export_20260319_094434.xlsx';
if (!file_exists($p)) {
    echo "MISSING: {$p}\n";
    exit(2);
}
$spreadsheet = IOFactory::load($p);
$sheet = $spreadsheet->getActiveSheet();
$rows = $sheet->toArray(null, true, true, true);
$max = min(20, count($rows));
for ($i = 1; $i <= $max; $i++) {
    $r = $rows[$i];
    echo "ROW {$i}: ";
    foreach ($r as $col => $cell) {
        $c = trim((string) $cell);
        $c = str_replace("\n", "\\n", $c);
        echo "{$col}='{" . $c . "}' ";
    }
    echo PHP_EOL;
}
