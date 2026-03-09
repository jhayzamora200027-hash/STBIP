<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('region_items', function (Blueprint $table) {
            $table->id();
            $table->foreignId('region_id')->constrained('regions')->cascadeOnDelete();
            $table->string('title');
            $table->string('province')->nullable();
            $table->string('municipality')->nullable();
            $table->boolean('with_expr')->default(false);
            $table->boolean('with_moa')->default(false);
            $table->year('year_of_moa')->nullable();
            $table->boolean('with_res')->default(false);
            $table->boolean('included_aip')->default(false);
            $table->boolean('with_adopted')->default(false);
            $table->boolean('with_replicated')->default(false);
            $table->enum('status', ['ongoing', 'dissolved'])->nullable();
            $table->timestamps();

            $table->index(['region_id', 'province']);
            $table->index(['region_id', 'municipality']);
            $table->index(['region_id', 'year_of_moa']);
            $table->index(['title']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('region_items');
    }
};