<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        if (Schema::hasTable('gallery_cards') && !Schema::hasColumn('gallery_cards', 'created_by')) {
            Schema::table('gallery_cards', function (Blueprint $table) {
                $table->string('created_by')->nullable()->after('is_active');
            });
        }
    }

    public function down()
    {
        if (Schema::hasTable('gallery_cards') && Schema::hasColumn('gallery_cards', 'created_by')) {
            Schema::table('gallery_cards', function (Blueprint $table) {
                $table->dropColumn('created_by');
            });
        }
    }
};
