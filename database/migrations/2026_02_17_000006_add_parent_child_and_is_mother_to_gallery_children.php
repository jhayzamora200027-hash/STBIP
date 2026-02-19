<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        if (Schema::hasTable('gallery_children')) {
            Schema::table('gallery_children', function (Blueprint $table) {
                if (! Schema::hasColumn('gallery_children', 'parent_child_id')) {
                    $table->unsignedBigInteger('parent_child_id')->nullable()->after('gallery_card_id');
                    $table->boolean('is_mother')->default(false)->after('is_active');
                }
            });

            // add FK for parent_child_id (self reference) — wrapped in try/catch to avoid duplicate-FK errors
            Schema::table('gallery_children', function (Blueprint $table) {
                if (Schema::hasColumn('gallery_children', 'parent_child_id')) {
                    try {
                        $table->foreign('parent_child_id')->references('id')->on('gallery_children')->onDelete('cascade');
                    } catch (\Exception $e) {
                        // FK may already exist or cannot be created; ignore
                    }
                }
            });
        }
    }

    public function down()
    {
        if (Schema::hasTable('gallery_children')) {
            Schema::table('gallery_children', function (Blueprint $table) {
                if (Schema::hasColumn('gallery_children', 'parent_child_id')) {
                    // drop foreign key if exists
                    try { $table->dropForeign(['parent_child_id']); } catch (\Exception $e) {}
                    $table->dropColumn('parent_child_id');
                }
                if (Schema::hasColumn('gallery_children', 'is_mother')) {
                    $table->dropColumn('is_mother');
                }
            });
        }
    }
};