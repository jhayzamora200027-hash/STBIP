<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up()
    {
        Schema::table('users', function (Blueprint $table) {
    $table->dropColumn('test');
});
    }

    public function down()
    {
        // Column deletion cannot be reversed automatically.;
    }
};
