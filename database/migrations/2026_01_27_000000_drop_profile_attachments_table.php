<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::dropIfExists('profile_attachments');
    }

    public function down(): void
    {
        // No rollback for drop
    }
};
