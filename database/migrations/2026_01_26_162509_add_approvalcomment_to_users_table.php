<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up()
    {
        if (!Schema::hasColumn('users', 'approvalcomment')) {
            Schema::table('users', function (Blueprint $table) {
                $table->string('approvalcomment')->nullable();
            });
        }
    }

    public function down()
    {
        Schema::table('users', function (Blueprint $table) {
        if (Schema::hasColumn('users', 'approvalcomment')) {
            $table->dropColumn('approvalcomment');
        }
    });
    }
};
