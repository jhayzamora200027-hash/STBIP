<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up()
    {
        if (Schema::hasTable('stsattachment') && !Schema::hasColumn('stsattachment', 'action')) {
            Schema::table('stsattachment', function (Blueprint $table) {
                $table->string('action', 20)->default('added')->after('created_by');
            });
        }
    }

    public function down()
    {
        if (Schema::hasTable('stsattachment') && Schema::hasColumn('stsattachment', 'action')) {
            Schema::table('stsattachment', function (Blueprint $table) {
                $table->dropColumn('action');
            });
        }
    }
};
