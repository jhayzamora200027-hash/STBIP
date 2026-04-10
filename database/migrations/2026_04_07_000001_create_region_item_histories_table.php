<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('region_item_histories', function (Blueprint $table) {
            $table->id();
            $table->foreignId('region_item_id')->nullable()->constrained('region_items')->nullOnDelete();
            $table->foreignId('region_id')->nullable()->constrained('regions')->nullOnDelete();
            $table->string('region_name')->nullable();
            $table->string('st_title');
            $table->string('province')->nullable();
            $table->string('city')->nullable();
            $table->string('updated_by')->nullable();
            $table->enum('action', ['add', 'update', 'delete']);
            $table->text('update_row')->nullable();
            $table->timestamps();

            $table->index('created_at');
            $table->index(['action', 'created_at']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('region_item_histories');
    }
};