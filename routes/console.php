<?php

use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use App\Support\PlainTextSanitizer;

Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote');

Artisan::command('security:sanitize-stored-inputs', function () {
    $targets = [
        'users' => ['name', 'firstname', 'middlename', 'lastname', 'phonenumber', 'address', 'approvalcomment', 'approvedby'],
        'region_items' => ['title', 'province', 'municipality', 'inactive_remarks', 'createdby', 'updatedby'],
        'region_item_histories' => ['region_name', 'st_title', 'province', 'city', 'updated_by', 'update_row'],
        'stsattachment' => ['region', 'province', 'municipality', 'title', 'original_filename', 'created_by'],
        'social_technology_titles' => ['social_technology', 'description', 'objectives', 'components', 'pilot_areas', 'status_remarks', 'resolution'],
        'gallery_cards' => ['title', 'description', 'icon_class', 'url', 'status', 'created_by', 'updated_by'],
        'gallery_children' => ['title', 'description', 'url', 'status', 'created_by', 'updated_by'],
        'approval_histories' => ['applicant_name', 'applicant_email', 'reviewed_by_name', 'reviewed_by_email', 'assigned_usergroup', 'rejection_reason'],
        'child_docno_histories' => ['docno', 'previous_docno', 'created_by', 'notes'],
    ];

    $totalRowsUpdated = 0;

    foreach ($targets as $table => $columns) {
        if (!Schema::hasTable($table)) {
            $this->line("Skipping {$table}: table not found.");
            continue;
        }

        $availableColumns = array_values(array_filter($columns, fn (string $column) => Schema::hasColumn($table, $column)));
        if ($availableColumns === []) {
            $this->line("Skipping {$table}: no target columns found.");
            continue;
        }

        $rowsUpdated = 0;
        DB::table($table)
            ->orderBy('id')
            ->select(array_merge(['id'], $availableColumns))
            ->chunkById(100, function ($rows) use ($table, $availableColumns, &$rowsUpdated, &$totalRowsUpdated) {
                foreach ($rows as $row) {
                    $updates = [];
                    foreach ($availableColumns as $column) {
                        $currentValue = $row->{$column};
                        $sanitizedValue = PlainTextSanitizer::sanitize($currentValue);
                        if ($sanitizedValue !== $currentValue) {
                            $updates[$column] = $sanitizedValue;
                        }
                    }

                    if ($updates === []) {
                        continue;
                    }

                    DB::table($table)->where('id', $row->id)->update($updates);
                    $rowsUpdated++;
                    $totalRowsUpdated++;
                }
            });

        $this->info("{$table}: sanitized {$rowsUpdated} row(s).");
    }

    $this->info("Sanitization complete. Updated {$totalRowsUpdated} row(s) total.");
})->purpose('Sanitize legacy stored text fields that may contain HTML or script payloads');
