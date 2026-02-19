<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::create('child_docno_histories', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('gallery_child_id');
            $table->unsignedBigInteger('gallery_card_id')->nullable();
            $table->string('docno');
            $table->string('previous_docno')->nullable();
            $table->string('created_by')->nullable();
            $table->text('notes')->nullable();
            $table->timestamps();

            $table->foreign('gallery_child_id')->references('id')->on('gallery_children')->onDelete('cascade');
            $table->foreign('gallery_card_id')->references('id')->on('gallery_cards')->onDelete('set null');
        });
    }

    public function down()
    {
        Schema::dropIfExists('child_docno_histories');
    }
};
