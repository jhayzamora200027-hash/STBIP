<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up()
    {
        Schema::table('uploadlogs', function (Blueprint $table) {
    $table->string('docno')->nullable();
});
    }

    public function down()
    {
        Schema::table('uploadlogs', function (Blueprint $table) {
    $table->dropColumn('docno');
});
    }
};
