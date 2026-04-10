<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('approval_histories', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->nullable()->constrained('users')->nullOnDelete();
            $table->string('applicant_name');
            $table->string('applicant_email')->index();
            $table->enum('action', ['approved', 'rejected'])->index();
            $table->string('reviewed_by_name')->nullable();
            $table->string('reviewed_by_email')->nullable();
            $table->string('assigned_usergroup')->nullable();
            $table->text('rejection_reason')->nullable();
            $table->timestamps();

            $table->index('created_at');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('approval_histories');
    }
};