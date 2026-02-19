<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::create('gallery_children', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('gallery_card_id');
            $table->string('title');
            $table->text('description')->nullable();
            $table->string('url')->nullable();
            $table->string('docno')->nullable();
            $table->integer('order')->default(0);
            $table->boolean('is_active')->default(true);
            $table->string('created_by')->nullable();
            $table->timestamps();

            $table->foreign('gallery_card_id')->references('id')->on('gallery_cards')->onDelete('cascade');
        });
    }

    public function down()
    {
        Schema::dropIfExists('gallery_children');
    }
};
