<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Schema;
use Tests\TestCase;

class InputMarkupGuardTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();

        Schema::dropIfExists('users');
        Schema::create('users', function (Blueprint $table) {
            $table->id();
            $table->string('name')->nullable();
            $table->string('firstname')->nullable();
            $table->string('middlename')->nullable();
            $table->string('lastname')->nullable();
            $table->string('email')->unique();
            $table->timestamp('email_verified_at')->nullable();
            $table->string('password');
            $table->string('user_id')->nullable();
            $table->string('usergroup')->nullable();
            $table->boolean('active')->default(true);
            $table->string('approvalstatus')->nullable();
            $table->string('phonenumber')->nullable();
            $table->string('gender')->nullable();
            $table->text('address')->nullable();
            $table->string('profile_picture_path')->nullable();
            $table->rememberToken()->nullable();
            $table->timestamps();
        });
    }

    protected function tearDown(): void
    {
        Schema::dropIfExists('users');

        parent::tearDown();
    }

    public function test_dashboard_filters_reject_markup_payloads(): void
    {
        $response = $this->getJson('/main?region%5B0%5D=%3Cscript%3Ealert(1)%3C%2Fscript%3E');

        $response->assertStatus(422);
        $response->assertJsonPath('message', 'Unsafe or potentially malicious input was detected. Remove HTML or script content and try again.');
        $response->assertJsonValidationErrors(['region.0']);
    }

    public function test_standard_request_redirects_with_security_prompt_when_markup_is_detected(): void
    {
        $response = $this->get('/main?region%5B0%5D=%3Cscript%3Ealert(1)%3C%2Fscript%3E');

        $response->assertRedirect('/main');
        $response->assertSessionHas('security_error', 'Unsafe or potentially malicious input was detected. Remove HTML or script content and try again.');
        $response->assertSessionHasErrors(['region.0']);
    }

    public function test_profile_update_rejects_markup_payloads(): void
    {
        $user = User::query()->create([
            'name' => 'Profile Tester',
            'firstname' => 'Profile',
            'lastname' => 'Tester',
            'email' => 'profile.tester@dswd.gov.ph',
            'password' => Hash::make('CurrentPass1!'),
            'user_id' => 'profile-tester',
            'usergroup' => 'admin',
            'active' => true,
            'approvalstatus' => 'A',
            'address' => 'Initial address',
        ]);

        $response = $this->actingAs($user)->putJson(route('profile.update'), [
            'email' => 'profile.tester@dswd.gov.ph',
            'usergroup' => 'admin',
            'current_password' => 'CurrentPass1!',
            'phonenumber' => '09171234567',
            'gender' => 'Male',
            'address' => '<script>alert(1)</script>',
        ]);

        $response->assertStatus(422);
        $response->assertJsonValidationErrors(['address']);

        $this->assertSame('Initial address', $user->fresh()->address);
    }
}