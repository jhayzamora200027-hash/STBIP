<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up()
    {
        if (!Schema::hasTable('stsattachment')) {
            Schema::create('stsattachment', function (Blueprint $table) {
                $table->bigIncrements('id');
                $table->string('region');
                $table->string('province')->nullable();
                $table->string('municipality')->nullable();
                $table->string('title');
                $table->string('year_of_moa')->nullable();
                $table->string('file_path');
                $table->string('original_filename');
                $table->string('mime_type')->nullable();
                $table->bigInteger('file_size')->nullable();
                $table->unsignedBigInteger('created_by')->nullable();
                $table->timestamps();
            });
        }
    }

    public function down()
    {
        Schema::dropIfExists('stsattachment');
    }
};
