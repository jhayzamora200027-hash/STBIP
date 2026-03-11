<?php

namespace App\Services;

use App\Models\Region;

class RegionDashboardDataService
{
    public function hasData(): bool
    {
        return Region::query()->whereHas('items')->exists();
    }

    public function getData(): array
    {
        $regions = Region::query()
            ->with(['items' => function ($query) {
                $query->orderBy('title')->orderBy('province')->orderBy('municipality');
            }])
            ->orderBy('name')
            ->get();

        $regionNames = [];
        $titles = [];
        $provinces = [];
        $municipalities = [];
        $years = [];
        $data = [];
        $regionMap = [];
        $headers = [
            'title of st',
            'province',
            'name of municipality',
            'with expression of interest',
            'with moa',
            'year of moa',
            'with resolution',
            'included aip',
            'adopted',
            'replicated',
            'status',
        ];

        foreach ($regions as $region) {
            $regionNames[] = $region->name;
            $regionMap[$region->name] = [
                'provinces' => [],
                'years' => [],
                'headers' => $headers,
            ];

            foreach ($region->items as $item) {
                $title = trim((string) $item->title);
                $province = trim((string) ($item->province ?? ''));
                $municipality = trim((string) ($item->municipality ?? ''));
                $yearOfMoa = $item->year_of_moa ? (string) $item->year_of_moa : null;

                if ($title === '') {
                    continue;
                }

                $titles[$title] = true;
                if ($province !== '') {
                    $provinces[$province] = true;
                }
                if ($municipality !== '') {
                    $municipalities[$municipality] = true;
                }
                if ($yearOfMoa !== null && $yearOfMoa !== '') {
                    $years[$yearOfMoa] = true;
                }

                $row = [
                    $title,
                    $province,
                    $municipality,
                    $item->with_expr,
                    $item->with_moa,
                    $yearOfMoa,
                    $item->with_res,
                    $item->included_aip,
                    $item->with_adopted,
                    $item->with_replicated,
                    $item->status,
                    // include year_of_resolution so frontend consumers can display SB resolution year
                    $item->year_of_resolution ?? null,
                ];

                $data[] = [
                    'region' => $region->name,
                    'title' => $title,
                    'province' => $province,
                    'municipality' => $municipality,
                    'with_expr' => $item->with_expr,
                    'with_moa' => $item->with_moa,
                    'with_res' => $item->with_res,
                    'included_aip' => $item->included_aip,
                    'with_adopted' => $item->with_adopted,
                    'with_replicated' => $item->with_replicated,
                    'year_of_moa' => $yearOfMoa,
                    'year_of_resolution' => $item->year_of_resolution ?? null,
                    'status' => $item->status,
                    'row' => $row,
                ];

                if ($province !== '' && $municipality !== '') {
                    if (!isset($regionMap[$region->name]['provinces'][$province])) {
                        $regionMap[$region->name]['provinces'][$province] = [];
                    }
                    if (!in_array($municipality, $regionMap[$region->name]['provinces'][$province], true)) {
                        $regionMap[$region->name]['provinces'][$province][] = $municipality;
                    }
                }

                if ($yearOfMoa !== null && $yearOfMoa !== '' && !in_array($yearOfMoa, $regionMap[$region->name]['years'], true)) {
                    $regionMap[$region->name]['years'][] = $yearOfMoa;
                }
            }
        }

        return [
            'regions' => $regionNames,
            'titles' => $titles,
            'provinces' => $provinces,
            'municipalities' => $municipalities,
            'years' => $years,
            'data' => $data,
            'regionMap' => $regionMap,
            'headers' => $headers,
        ];
    }
}