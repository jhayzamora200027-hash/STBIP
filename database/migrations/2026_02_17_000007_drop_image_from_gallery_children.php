<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        if (Schema::hasTable('gallery_children') && Schema::hasColumn('gallery_children', 'image')) {
            Schema::table('gallery_children', function (Blueprint $table) {
                $table->dropColumn('image');
            });
        }
    }

    public function down()
    {
        if (Schema::hasTable('gallery_children') && !Schema::hasColumn('gallery_children', 'image')) {
            Schema::table('gallery_children', function (Blueprint $table) {
                $table->string('image')->nullable()->after('description');
            });
        }
    }
};