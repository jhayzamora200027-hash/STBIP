<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up()
    {
        Schema::create('sector_utilities_logs', function (Blueprint $table) {
            $table->id();
            $table->string('action')->nullable();
            $table->string('user')->nullable();
            $table->text('details')->nullable();
            $table->unsignedBigInteger('gallery_card_id')->nullable();
            $table->timestamps();
            $table->index('gallery_card_id');
        });
    }

    public function down()
    {
        Schema::dropIfExists('sector_utilities_logs');
    }
};
