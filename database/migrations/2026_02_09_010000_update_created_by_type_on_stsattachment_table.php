<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up()
    {
        if (Schema::hasTable('stsattachment') && Schema::hasColumn('stsattachment', 'created_by')) {
            Schema::table('stsattachment', function (Blueprint $table) {
                $table->dropColumn('created_by');
            });
        }

        if (Schema::hasTable('stsattachment') && !Schema::hasColumn('stsattachment', 'created_by')) {
            Schema::table('stsattachment', function (Blueprint $table) {
                $table->string('created_by')->nullable();
            });
        }
    }

    public function down()
    {
        if (Schema::hasTable('stsattachment') && Schema::hasColumn('stsattachment', 'created_by')) {
            Schema::table('stsattachment', function (Blueprint $table) {
                $table->dropColumn('created_by');
            });
        }

        if (Schema::hasTable('stsattachment') && !Schema::hasColumn('stsattachment', 'created_by')) {
            Schema::table('stsattachment', function (Blueprint $table) {
                $table->unsignedBigInteger('created_by')->nullable();
            });
        }
    }
};
