<?php

namespace App\Services;

use App\Models\Region;
use App\Models\RegionItem;
use App\Support\MasterDataRegionCatalog;
use PhpOffice\PhpSpreadsheet\IOFactory;
use Illuminate\Support\Facades\DB;

class RegionSheetImportService
{
    public function importFromStoredExcel(string $storedFilename, ?string $actorName = null): array
    {
        $fullPath = storage_path('app/excels/' . $storedFilename);

        if (!file_exists($fullPath) || !is_readable($fullPath)) {
            throw new \RuntimeException('Stored Excel file not found or not readable.');
        }

        return $this->importFromPath($fullPath, $storedFilename, $actorName);
    }

    public function importFromPath(string $fullPath, ?string $sourceName = null, ?string $actorName = null): array
    {
        $actorName = trim((string) ($actorName ?: 'System'));
        $reader = IOFactory::createReaderForFile($fullPath);
        $reader->setReadDataOnly(true);
        $reader->setReadEmptyCells(false);
        $spreadsheet = $reader->load($fullPath);

        $rowsToInsert = [];
        $regionNames = [];

        foreach ($spreadsheet->getSheetNames() as $sheetName) {
            if (stripos($sheetName, 'Data CY 2020-2022') !== false) {
                continue;
            }

            $normalizedRegionName = MasterDataRegionCatalog::normalize($sheetName);
            if (!$normalizedRegionName) {
                continue;
            }

            $sheet = $spreadsheet->getSheetByName($sheetName);
            $rows = $sheet ? $sheet->toArray(null, false, false, false) : [];
            if (count($rows) < 2) {
                continue;
            }

            [$headers, $startSlice] = $this->extractHeaders($rows);
            if (empty($headers)) {
                continue;
            }

            $indexes = $this->resolveIndexes($headers);
            if ($indexes['title'] === null || $indexes['province'] === null || $indexes['municipality'] === null) {
                continue;
            }

            $regionNames[$normalizedRegionName] = true;

            foreach (array_slice($rows, $startSlice) as $row) {
                $title = $this->cleanString($row[$indexes['title']] ?? '');
                if ($title === '') {
                    continue;
                }

                $province = $this->cleanString($row[$indexes['province']] ?? '');
                $municipality = $this->cleanString($row[$indexes['municipality']] ?? '');
                $status = $this->resolveStatus($row, $indexes);
                $adoptionFlags = $this->resolveAdoptionFlags($row, $indexes);

                $rowsToInsert[] = [
                    'region_name' => $normalizedRegionName,
                    'title' => $title,
                    'province' => $province !== '' ? $province : null,
                    'municipality' => $municipality !== '' ? $municipality : null,
                    'with_expr' => $this->toBool($row[$indexes['with_expr']] ?? null),
                    'with_moa' => $this->toBool($row[$indexes['with_moa']] ?? null),
                    'year_of_moa' => $this->normalizeYear($row[$indexes['year_of_moa']] ?? null),
                    'with_res' => $this->toBool($row[$indexes['with_res']] ?? null),
                    'year_of_resolution' => $this->normalizeYear($row[$indexes['year_of_resolution']] ?? null),
                    'included_aip' => $this->toBool($row[$indexes['included_aip']] ?? null),
                    'with_adopted' => $adoptionFlags['with_adopted'],
                    'with_replicated' => $adoptionFlags['with_replicated'],
                    'status' => $status,
                ];
            }
        }

        if (empty($rowsToInsert)) {
            throw new \RuntimeException('No region data rows were found in the selected Google Sheet file.');
        }

        $stats = DB::transaction(function () use ($actorName, $regionNames, $rowsToInsert) {
            $regionIdMap = [];
            foreach (MasterDataRegionCatalog::all() as $regionName) {
                $region = Region::query()->firstOrCreate(
                    ['name' => $regionName],
                    ['createdby' => $actorName, 'updatedby' => $actorName]
                );

                $updates = [];
                if (!$region->createdby) {
                    $updates['createdby'] = $actorName;
                }
                if (!$region->updatedby) {
                    $updates['updatedby'] = $actorName;
                }
                if ($updates !== []) {
                    $region->forceFill($updates)->save();
                }

                $regionIdMap[$regionName] = $region->id;
            }

            $regionIds = array_values(array_unique(array_intersect_key($regionIdMap, $regionNames)));
            $existingItems = RegionItem::query()
                ->whereIn('region_id', $regionIds)
                ->get()
                ->keyBy(fn (RegionItem $item) => $this->buildIdentityKey(
                    $item->region_id,
                    $item->title,
                    $item->province,
                    $item->municipality
                ));

            $timestamp = now();
            $payload = [];
            $addedCount = 0;
            $updatedCount = 0;
            foreach ($rowsToInsert as $row) {
                if (!isset($regionIdMap[$row['region_name']])) {
                    continue;
                }

                $regionId = $regionIdMap[$row['region_name']];
                $record = [
                    'region_id' => $regionId,
                    'title' => $row['title'],
                    'province' => $row['province'],
                    'municipality' => $row['municipality'],
                    'with_expr' => $row['with_expr'],
                    'with_moa' => $row['with_moa'],
                    'year_of_moa' => $row['year_of_moa'],
                    'with_res' => $row['with_res'],
                    'year_of_resolution' => $row['with_res'] ? $row['year_of_resolution'] : null,
                    'included_aip' => $row['included_aip'],
                    'with_adopted' => $row['with_adopted'],
                    'with_replicated' => $row['with_replicated'],
                    'status' => $row['status'],
                    'updatedby' => $actorName,
                    'updated_at' => $timestamp,
                ];

                $identityKey = $this->buildIdentityKey(
                    $regionId,
                    $row['title'],
                    $row['province'],
                    $row['municipality']
                );

                $existingItem = $existingItems->get($identityKey);
                if ($existingItem) {
                    $existingItem->forceFill($record)->save();
                    $updatedCount++;
                    continue;
                }

                $payload[] = $record + [
                    'createdby' => $actorName,
                    'created_at' => $timestamp,
                ];
                $addedCount++;
            }

            foreach (array_chunk($payload, 500) as $chunk) {
                RegionItem::query()->insert($chunk);
            }

            return [
                'added_count' => $addedCount,
                'updated_count' => $updatedCount,
            ];
        });

        return [
            'source' => $sourceName ?: basename($fullPath),
            'regions_count' => count($regionNames),
            'items_count' => count($rowsToInsert),
            'added_count' => $stats['added_count'],
            'updated_count' => $stats['updated_count'],
        ];
    }

    private function buildIdentityKey(int $regionId, ?string $title, ?string $province, ?string $municipality): string
    {
        return implode('|', [
            $regionId,
            $this->normalizeIdentityValue($title),
            $this->normalizeIdentityValue($province),
            $this->normalizeIdentityValue($municipality),
        ]);
    }

    private function normalizeIdentityValue(?string $value): string
    {
        return mb_strtolower(trim((string) $value));
    }

    private function extractHeaders(array $rows): array
    {
        $headerRowIdx = null;
        foreach (range(0, min(4, count($rows) - 1)) as $i) {
            $trial = array_map(fn ($value) => strtolower(trim((string) $value)), $rows[$i]);
            if (
                array_search('title of st', $trial, true) !== false &&
                array_search('province', $trial, true) !== false &&
                array_search('name of municipality', $trial, true) !== false
            ) {
                $headerRowIdx = $i;
                break;
            }
        }

        if ($headerRowIdx === null) {
            return [[], 0];
        }

        $header = array_map(fn ($value) => strtolower(trim((string) $value)), $rows[$headerRowIdx]);
        $startSlice = $headerRowIdx + 1;

        if (isset($rows[$headerRowIdx + 1])) {
            $header2 = array_map(fn ($value) => strtolower(trim((string) $value)), $rows[$headerRowIdx + 1]);
            $combined = [];
            $max = max(count($header), count($header2));

            for ($i = 0; $i < $max; $i++) {
                $h1 = $header[$i] ?? '';
                $h2 = $header2[$i] ?? '';
                $combined[] = trim(($h1 && $h2) ? ($h1 . ' ' . $h2) : ($h1 . $h2));
            }

            $header = $combined;
            $startSlice = $headerRowIdx + 2;
        }

        return [$header, $startSlice];
    }

    private function resolveIndexes(array $headers): array
    {
        $indexes = [
            'title' => null,
            'province' => null,
            'municipality' => null,
            'with_expr' => null,
            'with_moa' => null,
            'year_of_moa' => null,
            'with_res' => null,
            'year_of_resolution' => null,
            'included_aip' => null,
            'with_adopted' => null,
            'with_replicated' => null,
            'adoption_status' => null,
            'status' => null,
            'ongoing' => null,
            'dissolved' => null,
        ];

        foreach ($headers as $index => $header) {
            if ($indexes['title'] === null && stripos($header, 'title') !== false) {
                $indexes['title'] = $index;
            }
            if ($indexes['province'] === null && stripos($header, 'province') !== false) {
                $indexes['province'] = $index;
            }
            if ($indexes['municipality'] === null && (stripos($header, 'municipality') !== false || stripos($header, 'city') !== false)) {
                $indexes['municipality'] = $index;
            }
            if ($indexes['with_expr'] === null && (stripos($header, 'expression') !== false || stripos($header, 'expr') !== false || stripos($header, 'interest') !== false)) {
                $indexes['with_expr'] = $index;
            }
            if ($indexes['year_of_moa'] === null && stripos($header, 'year') !== false) {
                $indexes['year_of_moa'] = $index;
            }
            if ($indexes['with_moa'] === null && stripos($header, 'moa') !== false && stripos($header, 'year') === false) {
                $indexes['with_moa'] = $index;
            }
            if ($indexes['with_res'] === null && stripos($header, 'year') === false && (stripos($header, 'resolution') !== false || stripos($header, 'res ') !== false || stripos($header, 'sb') !== false)) {
                $indexes['with_res'] = $index;
            }
            if ($indexes['year_of_resolution'] === null && stripos($header, 'resolution') !== false && stripos($header, 'year') !== false) {
                $indexes['year_of_resolution'] = $index;
            }
            if ($indexes['included_aip'] === null && stripos($header, 'aip') !== false) {
                $indexes['included_aip'] = $index;
            }
            if ($indexes['adoption_status'] === null && stripos($header, 'adopt') !== false && stripos($header, 'replic') !== false) {
                $indexes['adoption_status'] = $index;
            }
            if ($indexes['with_adopted'] === null && stripos($header, 'adopt') !== false) {
                $indexes['with_adopted'] = $index;
            }
            if ($indexes['with_replicated'] === null && stripos($header, 'replic') !== false && stripos($header, 'adopt') === false) {
                $indexes['with_replicated'] = $index;
            }
            if ($indexes['status'] === null && stripos($header, 'status') !== false) {
                $indexes['status'] = $index;
            }
            if ($indexes['ongoing'] === null && stripos($header, 'ongoing') !== false) {
                $indexes['ongoing'] = $index;
            }
            if ($indexes['dissolved'] === null && (stripos($header, 'dissolved') !== false || stripos($header, 'inactive') !== false || stripos($header, 'completed') !== false)) {
                $indexes['dissolved'] = $index;
            }
        }

        if ($indexes['with_replicated'] === null) {
            $indexes['with_replicated'] = $indexes['adoption_status'];
        }

        return $indexes;
    }

    private function resolveAdoptionFlags(array $row, array $indexes): array
    {
        $adoptedIndex = $indexes['with_adopted'] ?? null;
        $replicatedIndex = $indexes['with_replicated'] ?? null;
        $adoptionStatusIndex = $indexes['adoption_status'] ?? null;

        $adopted = $adoptedIndex !== null ? $this->toBool($row[$adoptedIndex] ?? null) : false;
        $replicated = $replicatedIndex !== null ? $this->toBool($row[$replicatedIndex] ?? null) : false;

        if ($adoptionStatusIndex !== null && ($adoptedIndex === null || $replicatedIndex === null || $adoptedIndex === $replicatedIndex)) {
            $rawStatus = strtolower($this->cleanString($row[$adoptionStatusIndex] ?? ''));

            if (str_contains($rawStatus, 'adopt')) {
                return ['with_adopted' => true, 'with_replicated' => false];
            }

            if (str_contains($rawStatus, 'replic')) {
                return ['with_adopted' => false, 'with_replicated' => true];
            }
        }

        return [
            'with_adopted' => $adopted,
            'with_replicated' => $replicated,
        ];
    }

    private function resolveStatus(array $row, array $indexes): ?string
    {
        $rawStatus = strtolower($this->cleanString($row[$indexes['status']] ?? ''));
        if ($rawStatus !== '') {
            if (str_contains($rawStatus, 'ongoing') || $rawStatus === 'on going') {
                return 'ongoing';
            }
            if (str_contains($rawStatus, 'dissolved') || str_contains($rawStatus, 'inactive') || str_contains($rawStatus, 'completed')) {
                return 'dissolved';
            }
        }

        if ($indexes['ongoing'] !== null && $this->toBool($row[$indexes['ongoing']] ?? null)) {
            return 'ongoing';
        }
        if ($indexes['dissolved'] !== null && $this->toBool($row[$indexes['dissolved']] ?? null)) {
            return 'dissolved';
        }

        return null;
    }

    private function cleanString(mixed $value): string
    {
        $string = is_scalar($value) ? (string) $value : '';
        $string = preg_replace('/[\x00-\x1F\x7F]+/u', '', $string);
        return trim($string ?? '');
    }

    private function toBool(mixed $value): bool
    {
        if (is_bool($value)) {
            return $value;
        }
        if (is_numeric($value)) {
            return (float) $value > 0;
        }

        $normalized = strtolower(trim((string) $value));
        if ($normalized === '' || $normalized === '0' || $normalized === 'no' || $normalized === 'false' || $normalized === 'n') {
            return false;
        }

        return in_array($normalized, ['1', 'true', 'yes', 'y', 'x', 'included', 'adopted', 'replicated'], true);
    }

    private function normalizeYear(mixed $value): ?int
    {
        if ($value === null || $value === '') {
            return null;
        }

        if (is_numeric($value)) {
            $year = (int) $value;
            return ($year >= 1900 && $year <= 2100) ? $year : null;
        }

        if (preg_match('/\b(19|20)\d{2}\b/', (string) $value, $matches)) {
            return (int) $matches[0];
        }

        return null;
    }
}