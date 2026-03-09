<?php

namespace App\Support;

class MasterDataRegionCatalog
{
    public const REGIONS = [
        'FO NCR',
        'FO CAR',
        'FO I',
        'FO II',
        'FO III',
        'FO IV-A',
        'FO IV-B',
        'FO V',
        'FO VI',
        'FO VII',
        'FO VIII',
        'FO IX',
        'FO X',
        'FO XI',
        'FO XII',
        'FO CARAGA',
        'FO BARMM',
        'FO NIR',
    ];

    public static function all(): array
    {
        return self::REGIONS;
    }

    public static function orderOf(?string $name): int
    {
        $index = array_search($name, self::REGIONS, true);

        return $index === false ? PHP_INT_MAX : $index;
    }

    public static function normalize(?string $name): ?string
    {
        $value = strtoupper(trim((string) $name));
        $value = preg_replace('/\s+/', ' ', $value ?? '');

        if ($value === '') {
            return null;
        }

        $aliases = [
            'NCR' => 'FO NCR',
            'FO NCR' => 'FO NCR',
            'CAR' => 'FO CAR',
            'FO CAR' => 'FO CAR',
            'FO I' => 'FO I',
            'FO II' => 'FO II',
            'FO III' => 'FO III',
            'FO IV-A' => 'FO IV-A',
            'FO IVA' => 'FO IV-A',
            'FO IV B' => 'FO IV-B',
            'FO IV-B' => 'FO IV-B',
            'FO V' => 'FO V',
            'FO VI' => 'FO VI',
            'FO VII' => 'FO VII',
            'FO VIII' => 'FO VIII',
            'FO IX' => 'FO IX',
            'FO X' => 'FO X',
            'FO XI' => 'FO XI',
            'FO XII' => 'FO XII',
            'FO XIII' => 'FO CARAGA',
            'CARAGA' => 'FO CARAGA',
            'FO CARAGA' => 'FO CARAGA',
            'BARMM' => 'FO BARMM',
            'FO BARMM' => 'FO BARMM',
            'BANGSAMORO' => 'FO BARMM',
            'NIR' => 'FO NIR',
            'FO NIR' => 'FO NIR',
            'NEGROS ISLAND REGION' => 'FO NIR',
        ];

        return $aliases[$value] ?? (in_array($value, self::REGIONS, true) ? $value : null);
    }
}