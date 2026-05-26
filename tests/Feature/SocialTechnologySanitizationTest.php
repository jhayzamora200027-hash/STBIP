<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Schema;
use Tests\TestCase;

class SocialTechnologySanitizationTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();

        Schema::dropIfExists('social_technology_titles');
        Schema::dropIfExists('users');

        Schema::create('users', function (Blueprint $table) {
            $table->id();
            $table->string('name')->nullable();
            $table->string('email')->unique();
            $table->string('password')->nullable();
            $table->string('user_id')->nullable();
            $table->string('usergroup')->nullable();
            $table->string('approvalstatus')->nullable();
            $table->rememberToken()->nullable();
            $table->timestamps();
        });

        Schema::create('social_technology_titles', function (Blueprint $table) {
            $table->id();
            $table->string('createdby')->nullable();
            $table->string('updatedby')->nullable();
            $table->string('sector')->nullable();
            $table->text('laws_and_issuances')->nullable();
            $table->string('social_technology')->unique();
            $table->text('description')->nullable();
            $table->text('objectives')->nullable();
            $table->text('components')->nullable();
            $table->text('pilot_areas')->nullable();
            $table->string('year_implemented')->nullable();
            $table->text('status_remarks')->nullable();
            $table->text('resolution')->nullable();
            $table->text('guidelines')->nullable();
            $table->text('program_manual_outline')->nullable();
            $table->text('information_systems_developed')->nullable();
            $table->text('session_guide_key_topics')->nullable();
            $table->text('training_manual_outline')->nullable();
            $table->timestamps();
        });
    }

    protected function tearDown(): void
    {
        Schema::dropIfExists('social_technology_titles');
        Schema::dropIfExists('users');

        parent::tearDown();
    }

    public function test_social_technology_import_skips_rows_with_markup(): void
    {
        $admin = User::query()->create([
            'name' => 'Social Tech Admin',
            'email' => 'socialtech.admin@dswd.gov.ph',
            'password' => Hash::make('password'),
            'user_id' => 'socialtech-admin',
            'usergroup' => 'admin',
            'approvalstatus' => 'A',
        ]);

        $file = UploadedFile::fake()->createWithContent(
            'titles.csv',
            "Social Technology,Description\n<script>alert(1)</script>,Sample description\n"
        );

        $response = $this->actingAs($admin)->post(route('socialtech.import'), [
            'csv_file' => $file,
        ]);

        $response->assertRedirect(route('STDashboard'));
        $response->assertSessionHas('status', fn (string $status) => str_contains($status, 'skipped 1 invalid row'));
        $this->assertDatabaseCount('social_technology_titles', 0);
    }
}