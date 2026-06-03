<?php

namespace App\Services;

use App\Models\Region;
use App\Models\StsAttachment;
use App\Models\User;

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
                    // include inactive info
                    $item->inactive_status ?? null,
                    $item->inactive_remarks ?? null,
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
                    'inactive_status' => $item->inactive_status ?? null,
                    'inactive_remarks' => $item->inactive_remarks ?? null,
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

        $data = $this->appendAttachmentInfo($data);

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

    private function appendAttachmentInfo(array $rows): array
    {
        if ($rows === []) {
            return $rows;
        }

        $rowKeys = [];
        foreach ($rows as $row) {
            $rowKeys[$this->buildAttachmentIdentityKey([
                'region' => $row['region'] ?? null,
                'province' => $row['province'] ?? null,
                'municipality' => $row['municipality'] ?? null,
                'title' => $row['title'] ?? null,
                'year_of_moa' => $row['year_of_moa'] ?? null,
            ])] = true;
        }

        $attachments = StsAttachment::query()
            ->select([
                'id',
                'region',
                'province',
                'municipality',
                'title',
                'year_of_moa',
                'file_path',
                'original_filename',
                'file_size',
                'action',
                'created_by',
            ])
            ->orderBy('id')
            ->get();

        if ($attachments->isEmpty()) {
            return $rows;
        }

        $relevantAttachments = $attachments->filter(function (StsAttachment $attachment) use ($rowKeys) {
            $key = $this->buildAttachmentIdentityKey([
                'region' => $attachment->region,
                'province' => $attachment->province,
                'municipality' => $attachment->municipality,
                'title' => $attachment->title,
                'year_of_moa' => $attachment->year_of_moa,
            ]);

            return isset($rowKeys[$key]);
        })->values();

        if ($relevantAttachments->isEmpty()) {
            return $rows;
        }

        $userNames = [];
        $userIds = $relevantAttachments->pluck('created_by')->filter()->unique()->values();
        if ($userIds->isNotEmpty()) {
            $userNames = User::query()
                ->whereKey($userIds->all())
                ->pluck('name', 'id')
                ->toArray();
        }

        $attachmentMap = [];
        foreach ($relevantAttachments as $attachment) {
            $key = $this->buildAttachmentIdentityKey([
                'region' => $attachment->region,
                'province' => $attachment->province,
                'municipality' => $attachment->municipality,
                'title' => $attachment->title,
                'year_of_moa' => $attachment->year_of_moa,
            ]);
            $fileKey = implode('|', [
                trim((string) $attachment->file_path),
                trim((string) $attachment->original_filename),
                trim((string) $attachment->file_size),
            ]);

            if (!isset($attachmentMap[$key])) {
                $attachmentMap[$key] = [];
            }

            if (!isset($attachmentMap[$key][$fileKey]) || $attachment->id > $attachmentMap[$key][$fileKey]['id']) {
                $attachmentMap[$key][$fileKey] = [
                    'id' => $attachment->id,
                    'action' => $attachment->action,
                    'url' => $attachment->action === 'added' ? route('sts.attachments.show', $attachment->id) : null,
                    'uploaded_by' => $userNames[$attachment->created_by] ?? $attachment->created_by,
                    'original_filename' => $attachment->original_filename,
                ];
            }
        }

        foreach ($rows as &$row) {
            $key = $this->buildAttachmentIdentityKey([
                'region' => $row['region'] ?? null,
                'province' => $row['province'] ?? null,
                'municipality' => $row['municipality'] ?? null,
                'title' => $row['title'] ?? null,
                'year_of_moa' => $row['year_of_moa'] ?? null,
            ]);

            if (!isset($attachmentMap[$key])) {
                continue;
            }

            $activeAttachments = collect($attachmentMap[$key])
                ->filter(fn (array $entry) => $entry['action'] === 'added' && !empty($entry['url']))
                ->sortByDesc(fn (array $entry) => $entry['id'])
                ->map(fn (array $entry) => [
                    'id' => $entry['id'],
                    'url' => $entry['url'],
                    'uploaded_by' => $entry['uploaded_by'],
                    'original_filename' => $entry['original_filename'],
                ])
                ->values()
                ->all();

            if ($activeAttachments === []) {
                continue;
            }

            $row['attachments'] = $activeAttachments;
            $row['attachment_id'] = $activeAttachments[0]['id'];
            $row['attachment_url'] = $activeAttachments[0]['url'];
            $row['attachment_uploaded_by'] = $activeAttachments[0]['uploaded_by'];
        }
        unset($row);

        return $rows;
    }

    private function buildAttachmentIdentityKey(array $identity): string
    {
        return implode('|', [
            trim((string) ($identity['region'] ?? '')),
            trim((string) ($identity['province'] ?? '')),
            trim((string) ($identity['municipality'] ?? '')),
            trim((string) ($identity['title'] ?? '')),
            trim((string) ($identity['year_of_moa'] ?? '')),
        ]);
    }
}