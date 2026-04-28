<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('social_technology_logs', function (Blueprint $table) {
            $table->id();
            $table->string('action')->nullable();
            $table->string('performed_by')->nullable();
            $table->text('details')->nullable();
            $table->unsignedBigInteger('social_technology_title_id')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('social_technology_logs');
    }
};
