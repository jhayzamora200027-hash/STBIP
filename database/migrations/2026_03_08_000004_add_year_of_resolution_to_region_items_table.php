<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('region_items', function (Blueprint $table) {
            $table->year('year_of_resolution')->nullable()->after('with_res');
        });
    }

    public function down(): void
    {
        Schema::table('region_items', function (Blueprint $table) {
            $table->dropColumn('year_of_resolution');
        });
    }
};