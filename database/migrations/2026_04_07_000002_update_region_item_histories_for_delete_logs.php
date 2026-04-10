<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (!Schema::hasTable('region_item_histories')) {
            return;
        }

        if (Schema::getConnection()->getDriverName() === 'sqlite') {
            $this->rebuildTableForSqlite(includeDeleteAction: true);
            return;
        }

        Schema::table('region_item_histories', function (Blueprint $table) {
            $table->dropForeign(['region_item_id']);
        });

        DB::statement('ALTER TABLE region_item_histories MODIFY region_item_id BIGINT UNSIGNED NULL');
        DB::statement("ALTER TABLE region_item_histories MODIFY action ENUM('add', 'update', 'delete') NOT NULL");

        Schema::table('region_item_histories', function (Blueprint $table) {
            $table->foreign('region_item_id')->references('id')->on('region_items')->nullOnDelete();
        });
    }

    public function down(): void
    {
        if (!Schema::hasTable('region_item_histories')) {
            return;
        }

        if (Schema::getConnection()->getDriverName() === 'sqlite') {
            $this->rebuildTableForSqlite(includeDeleteAction: false);
            return;
        }

        DB::table('region_item_histories')
            ->whereNull('region_item_id')
            ->orWhere('action', 'delete')
            ->delete();

        Schema::table('region_item_histories', function (Blueprint $table) {
            $table->dropForeign(['region_item_id']);
        });

        DB::statement('ALTER TABLE region_item_histories MODIFY region_item_id BIGINT UNSIGNED NOT NULL');
        DB::statement("ALTER TABLE region_item_histories MODIFY action ENUM('add', 'update') NOT NULL");

        Schema::table('region_item_histories', function (Blueprint $table) {
            $table->foreign('region_item_id')->references('id')->on('region_items')->cascadeOnDelete();
        });
    }

    private function rebuildTableForSqlite(bool $includeDeleteAction): void
    {
        Schema::disableForeignKeyConstraints();

        Schema::rename('region_item_histories', 'region_item_histories_old');

        Schema::create('region_item_histories', function (Blueprint $table) use ($includeDeleteAction) {
            $table->id();
            $regionItemColumn = $table->foreignId('region_item_id');
            if ($includeDeleteAction) {
                $regionItemColumn->nullable();
            }
            $regionItemColumn->constrained('region_items')->{$includeDeleteAction ? 'nullOnDelete' : 'cascadeOnDelete'}();

            $table->foreignId('region_id')->nullable()->constrained('regions')->nullOnDelete();
            $table->string('region_name')->nullable();
            $table->string('st_title');
            $table->string('province')->nullable();
            $table->string('city')->nullable();
            $table->string('updated_by')->nullable();
            $table->string('action');
            $table->text('update_row')->nullable();
            $table->timestamps();

            $table->index('created_at');
            $table->index(['action', 'created_at']);
        });

        $query = DB::table('region_item_histories_old')->select([
            'id',
            'region_item_id',
            'region_id',
            'region_name',
            'st_title',
            'province',
            'city',
            'updated_by',
            'action',
            'update_row',
            'created_at',
            'updated_at',
        ]);

        if (!$includeDeleteAction) {
            $query->whereNotNull('region_item_id')
                ->whereIn('action', ['add', 'update']);
        }

        DB::table('region_item_histories')->insertUsing([
            'id',
            'region_item_id',
            'region_id',
            'region_name',
            'st_title',
            'province',
            'city',
            'updated_by',
            'action',
            'update_row',
            'created_at',
            'updated_at',
        ], $query);

        Schema::drop('region_item_histories_old');
        Schema::enableForeignKeyConstraints();
    }
};