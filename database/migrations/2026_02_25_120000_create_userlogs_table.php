<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up()
    {
        Schema::create('userlogs', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('user_id')->nullable(); // affected user
            $table->string('action'); // created, updated, profile_updated, etc.
            $table->unsignedBigInteger('performed_by')->nullable(); // who did it
            $table->json('meta')->nullable(); // additional info like changed fields
            $table->timestamps();

            $table->index('user_id');
            $table->index('performed_by');
        });
    }

    public function down()
    {
        Schema::dropIfExists('userlogs');
    }
};