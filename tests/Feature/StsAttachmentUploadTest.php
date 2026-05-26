<?php

namespace Tests\Feature;

use App\Models\StsAttachment;
use App\Models\User;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class StsAttachmentUploadTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();

        Schema::dropIfExists('users');
        Schema::create('users', function (Blueprint $table) {
            $table->id();
            $table->string('name')->nullable();
            $table->string('email')->unique();
            $table->timestamp('email_verified_at')->nullable();
            $table->string('password');
            $table->string('user_id')->nullable();
            $table->string('usergroup')->nullable();
            $table->rememberToken()->nullable();
            $table->timestamps();
        });

        Schema::dropIfExists('stsattachment');
        Schema::create('stsattachment', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->string('region');
            $table->string('province')->nullable();
            $table->string('municipality')->nullable();
            $table->string('title');
            $table->string('year_of_moa')->nullable();
            $table->string('file_path');
            $table->string('original_filename');
            $table->string('mime_type')->nullable();
            $table->bigInteger('file_size')->nullable();
            $table->string('created_by')->nullable();
            $table->string('action')->nullable();
            $table->timestamps();
        });
    }

    protected function tearDown(): void
    {
        Schema::dropIfExists('stsattachment');
        Schema::dropIfExists('users');

        parent::tearDown();
    }

    public function test_it_uploads_multiple_pdf_attachments_for_one_item(): void
    {
        Storage::fake('public');

        $user = User::query()->create([
            'name' => 'Attachment Tester',
            'email' => 'attachments@example.com',
            'password' => Hash::make('password'),
            'user_id' => 'tester-001',
            'usergroup' => 'sysadmin',
        ]);

        $response = $this->actingAs($user)->post(route('sts.attachments.store'), [
            'region' => 'FO NCR',
            'province' => 'NCR',
            'municipality' => 'Quezon City',
            'title' => 'Sample ST',
            'year_of_moa' => '2026',
            'attachments' => [
                UploadedFile::fake()->create('moa.pdf', 100, 'application/pdf'),
                UploadedFile::fake()->create('resolution.pdf', 120, 'application/pdf'),
            ],
        ], [
            'X-Requested-With' => 'XMLHttpRequest',
            'Accept' => 'application/json',
        ]);

        $response->assertOk();
        $response->assertJsonPath('success', true);
        $response->assertJsonCount(2, 'attachments');
        $response->assertJsonPath('attachments.0.original_filename', 'moa.pdf');
        $response->assertJsonPath('attachments.1.original_filename', 'resolution.pdf');

        $this->assertDatabaseCount('stsattachment', 2);

        $storedAttachments = StsAttachment::query()->orderBy('id', 'asc')->get();
        $this->assertSame(['moa.pdf', 'resolution.pdf'], $storedAttachments->pluck('original_filename')->all());
        $this->assertTrue(
            $storedAttachments->every(fn (StsAttachment $attachment) => Storage::disk('public')->exists($attachment->file_path))
        );
    }

    public function test_it_rejects_markup_payloads_in_attachment_metadata(): void
    {
        Storage::fake('public');

        $user = User::query()->create([
            'name' => 'Attachment Tester',
            'email' => 'attachments@example.com',
            'password' => Hash::make('password'),
            'user_id' => 'tester-001',
            'usergroup' => 'sysadmin',
        ]);

        $response = $this->actingAs($user)->postJson(route('sts.attachments.store'), [
            'region' => 'FO NCR',
            'province' => 'NCR',
            'municipality' => 'Quezon City',
            'title' => '<script>alert(1)</script>',
            'year_of_moa' => '2026',
            'attachments' => [
                UploadedFile::fake()->create('moa.pdf', 100, 'application/pdf'),
            ],
        ]);

        $response->assertStatus(422);
        $response->assertJsonValidationErrors(['title']);
        $this->assertDatabaseCount('stsattachment', 0);
    }
}
