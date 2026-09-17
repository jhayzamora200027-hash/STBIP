<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up()
    {
        if (!Schema::hasColumn('uploadlogs', 'docno')) {
            Schema::table('uploadlogs', function (Blueprint $table) {
                $table->string('docno')->nullable();
            });
        }
    }

    public function down()
    {
        // Keep an existing staging column intact during rollback.
    }
};
