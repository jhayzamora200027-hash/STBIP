<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::table('gallery_cards', function (Blueprint $table) {
            $table->string('status')->default('On going')->after('is_active');
        });

        Schema::table('gallery_children', function (Blueprint $table) {
            $table->string('status')->default('On going')->after('is_active');
        });
    }

    public function down()
    {
        Schema::table('gallery_children', function (Blueprint $table) {
            $table->dropColumn('status');
        });

        Schema::table('gallery_cards', function (Blueprint $table) {
            $table->dropColumn('status');
        });
    }
};