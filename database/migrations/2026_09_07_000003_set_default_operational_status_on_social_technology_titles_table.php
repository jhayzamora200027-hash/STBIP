<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        DB::table('social_technology_titles')
            ->whereNull('operational_status')
            ->update(['operational_status' => 'Operational']);

        Schema::table('social_technology_titles', function (Blueprint $table) {
            $table->string('operational_status', 30)
                ->default('Operational')
                ->change();
        });
    }

    public function down(): void
    {
        Schema::table('social_technology_titles', function (Blueprint $table) {
            $table->string('operational_status', 30)
                ->nullable()
                ->change();
        });
    }
};
