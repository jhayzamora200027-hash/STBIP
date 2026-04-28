<?php

namespace Tests\Feature;

use Tests\TestCase;
use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;

class EmbedValidationTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();

        Schema::dropIfExists('selectdocslogs');
        Schema::create('selectdocslogs', function (Blueprint $table) {
            $table->id();
            $table->string('excelname')->nullable();
            $table->timestamps();
        });

        Schema::dropIfExists('region_items');
        Schema::dropIfExists('regions');

        Schema::create('regions', function (Blueprint $table) {
            $table->id();
            $table->string('name')->nullable();
            $table->timestamps();
        });

        Schema::create('region_items', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('region_id')->nullable()->index();
            $table->string('title')->nullable();
            $table->string('province')->nullable();
            $table->string('municipality')->nullable();
            $table->boolean('with_expr')->default(false);
            $table->boolean('with_moa')->default(false);
            $table->integer('year_of_moa')->nullable();
            $table->boolean('with_res')->default(false);
            $table->integer('year_of_resolution')->nullable();
            $table->boolean('included_aip')->default(false);
            $table->boolean('with_adopted')->default(false);
            $table->boolean('with_replicated')->default(false);
            $table->string('status')->nullable();
            $table->boolean('inactive_status')->nullable();
            $table->text('inactive_remarks')->nullable();
            $table->timestamps();
        });

        Schema::dropIfExists('gallery_children');
        Schema::dropIfExists('gallery_cards');

        Schema::create('gallery_cards', function (Blueprint $table) {
            $table->id();
            $table->string('docno')->nullable();
            $table->string('title')->nullable();
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });

        Schema::create('gallery_children', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('gallery_card_id')->nullable()->index();
            $table->unsignedBigInteger('parent_child_id')->nullable()->index();
            $table->string('docno')->nullable();
            $table->string('title')->nullable();
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });
    }

    public function test_embed_true_values()
    {
        $trueValues = ['1', 'true', 'yes', 'on'];
        foreach ($trueValues as $v) {
            $resp = $this->get('/streport?embed=' . urlencode($v));
            $resp->assertStatus(200);
            $resp->assertViewHas('embed', true);
        }
    }

    public function test_embed_invalid_values_treated_as_false()
    {
        $bad = ["1 OR 1=1", "'; DROP TABLE users; --", 'random', ''];
        foreach ($bad as $v) {
            $resp = $this->get('/streport' . ($v !== '' ? '?embed=' . urlencode($v) : ''));
            $resp->assertStatus(200);
            $resp->assertViewHas('embed', false);
        }
    }
}
