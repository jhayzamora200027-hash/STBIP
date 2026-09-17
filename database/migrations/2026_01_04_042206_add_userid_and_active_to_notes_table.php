<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        if (!Schema::hasColumn('notes', 'userid')) {
            Schema::table('notes', function (Blueprint $table) {
                $table->unsignedBigInteger('userid')->nullable()->after('createdby');
            });
        }

        if (!Schema::hasColumn('notes', 'active')) {
            Schema::table('notes', function (Blueprint $table) {
                $table->boolean('active')->default(true)->after('userid');
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Keep existing staging columns intact during rollback.
    }
};
