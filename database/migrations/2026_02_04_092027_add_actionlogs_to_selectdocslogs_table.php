<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up()
    {
        Schema::table('selectdocslogs', function (Blueprint $table) {
    $table->string('actionlogs')->nullable();
});
    }

    public function down()
    {
        Schema::table('selectdocslogs', function (Blueprint $table) {
    $table->dropColumn('actionlogs');
});
    }
};
