<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('regions', function (Blueprint $table) {
            $table->string('createdby')->nullable()->after('name');
            $table->string('updatedby')->nullable()->after('createdby');
        });

        Schema::table('region_items', function (Blueprint $table) {
            $table->string('createdby')->nullable()->after('status');
            $table->string('updatedby')->nullable()->after('createdby');
        });
    }

    public function down(): void
    {
        Schema::table('region_items', function (Blueprint $table) {
            $table->dropColumn(['createdby', 'updatedby']);
        });

        Schema::table('regions', function (Blueprint $table) {
            $table->dropColumn(['createdby', 'updatedby']);
        });
    }
};