<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     *
     * NOTE: this uses Schema::table()->change() which requires doctrine/dbal.
     */
    public function up()
    {
        Schema::table('social_technology_titles', function (Blueprint $table) {
            $table->string('year_implemented')->nullable()->change();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down()
    {
        Schema::table('social_technology_titles', function (Blueprint $table) {
            $table->integer('year_implemented')->nullable()->change();
        });
    }
};
