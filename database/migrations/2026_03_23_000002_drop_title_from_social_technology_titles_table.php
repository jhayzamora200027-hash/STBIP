<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class DropTitleFromSocialTechnologyTitlesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('social_technology_titles', function (Blueprint $table) {
            if (Schema::hasColumn('social_technology_titles', 'title')) {
                $table->dropColumn('title');
            }
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('social_technology_titles', function (Blueprint $table) {
            if (!Schema::hasColumn('social_technology_titles', 'title')) {
                $table->string('title')->nullable();
            }
        });
    }
}
