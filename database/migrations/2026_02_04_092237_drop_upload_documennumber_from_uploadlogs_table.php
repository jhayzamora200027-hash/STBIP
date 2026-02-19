<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up()
    {
        Schema::table('uploadlogs', function (Blueprint $table) {
    $table->dropColumn('upload_documennumber');
});
    }

    public function down()
    {
        // Column deletion cannot be reversed automatically.;
    }
};
