<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddFieldsToSocialTechnologyTitlesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('social_technology_titles', function (Blueprint $table) {
            $table->string('sector')->nullable();
            $table->text('laws_and_issuances')->nullable();
            $table->string('social_technology')->nullable();
            $table->text('description')->nullable();
            $table->text('objectives')->nullable();
            $table->text('components')->nullable();
            $table->string('pilot_areas')->nullable();
            $table->integer('year_implemented')->nullable();
            $table->text('status_remarks')->nullable();
            $table->text('resolution')->nullable();
            $table->string('guidelines')->nullable();
            $table->text('program_manual_outline')->nullable();
            $table->text('information_systems_developed')->nullable();
            $table->text('session_guide_key_topics')->nullable();
            $table->text('training_manual_outline')->nullable();
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
            $table->dropColumn([
                'sector',
                'laws_and_issuances',
                'social_technology',
                'description',
                'objectives',
                'components',
                'pilot_areas',
                'year_implemented',
                'status_remarks',
                'resolution',
                'guidelines',
                'program_manual_outline',
                'information_systems_developed',
                'session_guide_key_topics',
                'training_manual_outline',
            ]);
        });
    }
}
