<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up()
    {
        Schema::dropIfExists('testtable');
    }

    public function down()
    {
        // Table deletion cannot be reversed automatically.;
    }
};
