<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up()
    {
        if (!Schema::hasColumn('users', 'test')) {
            Schema::table('users', function (Blueprint $table) {
                $table->string('test')->nullable();
            });
        }
    }

    public function down()
    {
        if (Schema::hasColumn('users', 'test')) {
            Schema::table('users', function (Blueprint $table) {
                $table->dropColumn('test');
            });
        }
    }
};
