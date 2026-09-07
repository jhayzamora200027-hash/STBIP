<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('replication_records', function (Blueprint $table) {
            $table->id();
            $table->string('social_technology_title');
            $table->foreignId('user_id')->nullable()->constrained()->nullOnDelete();
            $table->text('redirect_url')->nullable();
            $table->timestamps();

            $table->index('social_technology_title');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('replication_records');
    }
};