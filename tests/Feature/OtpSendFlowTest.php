<?php

namespace Tests\Feature;

use App\Mail\OtpMail;
use App\Models\User;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Schema;
use Tests\TestCase;

class OtpSendFlowTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();

        config()->set('services.recaptcha.secret', '');
        config()->set('services.recaptcha.site_key', '');

        Schema::dropIfExists('users');

        Schema::create('users', function (Blueprint $table) {
            $table->id();
            $table->string('name')->nullable();
            $table->string('firstname')->nullable();
            $table->string('middlename')->nullable();
            $table->string('lastname')->nullable();
            $table->string('email')->unique();
            $table->string('password')->nullable();
            $table->string('user_id')->nullable();
            $table->string('usergroup')->nullable();
            $table->unsignedTinyInteger('active')->default(1);
            $table->string('approvalstatus')->nullable();
            $table->rememberToken();
            $table->timestamps();
        });

        Mail::fake();
    }

    protected function tearDown(): void
    {
        Schema::dropIfExists('users');

        parent::tearDown();
    }

    public function test_login_stages_otp_without_sending_email_until_confirmed(): void
    {
        $user = $this->makeApprovedUser();

        $loginResponse = $this->postJson(route('login'), [
            'email' => $user->email,
            'password' => 'Password123!',
        ]);

        $loginResponse->assertOk();
        $loginResponse->assertJson([
            'success' => true,
            'otp_required' => true,
            'otp_sent' => false,
            'send_count' => 0,
            'remaining_sends' => 3,
        ]);

        Mail::assertNothingSent();

        $sendResponse = $this->postJson(route('otp.send'));

        $sendResponse->assertOk();
        $sendResponse->assertJson([
            'success' => true,
            'otp_sent' => true,
            'send_count' => 1,
            'remaining_sends' => 2,
        ]);

        Mail::assertSent(OtpMail::class, 1);
    }

    public function test_fourth_otp_send_request_locks_the_pending_session_for_five_minutes(): void
    {
        $user = $this->makeApprovedUser('locked.user@dswd.gov.ph');

        $this->postJson(route('login'), [
            'email' => $user->email,
            'password' => 'Password123!',
        ])->assertOk();

        $this->postJson(route('otp.send'))->assertOk()->assertJson(['send_count' => 1]);
        $this->postJson(route('otp.send'))->assertOk()->assertJson(['send_count' => 2]);
        $this->postJson(route('otp.send'))->assertOk()->assertJson(['send_count' => 3]);

        $lockedResponse = $this->postJson(route('otp.send'));

        $lockedResponse->assertStatus(429);
        $lockedResponse->assertJson([
            'message' => 'OTP requests are temporarily restricted. Please wait 5 minutes before trying again.',
            'send_count' => 3,
            'remaining_sends' => 0,
        ]);
        $lockedResponse->assertJsonStructure(['locked_until', 'retry_after_seconds']);

        $reloginResponse = $this->postJson(route('login'), [
            'email' => $user->email,
            'password' => 'Password123!',
        ]);

        $reloginResponse->assertOk();
        $reloginResponse->assertJson([
            'otp_required' => true,
            'send_count' => 3,
            'remaining_sends' => 0,
        ]);
        $reloginResponse->assertJsonStructure(['locked_until', 'retry_after_seconds']);

        $this->postJson(route('otp.send'))->assertStatus(429);

        $this->postJson(route('otp.verify'), [
            'otp_code' => '123456',
        ])->assertStatus(429);

        $this->travel(5)->minutes();

        $unlockResponse = $this->postJson(route('otp.send'));

        $unlockResponse->assertOk();
        $unlockResponse->assertJson([
            'success' => true,
            'send_count' => 1,
            'remaining_sends' => 2,
        ]);

        Mail::assertSent(OtpMail::class, 4);
    }

    public function test_third_wrong_otp_code_locks_verification_for_five_minutes(): void
    {
        $user = $this->makeApprovedUser('wrong-otp.user@dswd.gov.ph');

        $this->postJson(route('login'), [
            'email' => $user->email,
            'password' => 'Password123!',
        ])->assertOk();

        $this->postJson(route('otp.send'))->assertOk();

        $this->postJson(route('otp.verify'), [
            'otp_code' => '111111',
        ])->assertStatus(422)->assertJson(['message' => 'The code is incorrect.']);

        $this->postJson(route('otp.verify'), [
            'otp_code' => '222222',
        ])->assertStatus(422)->assertJson(['message' => 'The code is incorrect.']);

        $lockedResponse = $this->postJson(route('otp.verify'), [
            'otp_code' => '333333',
        ]);

        $lockedResponse->assertStatus(429);
        $lockedResponse->assertJson([
            'message' => 'OTP input is temporarily restricted. Please wait 5 minutes before trying again.',
        ]);
        $lockedResponse->assertJsonStructure(['locked_until', 'retry_after_seconds']);

        $reloginResponse = $this->postJson(route('login'), [
            'email' => $user->email,
            'password' => 'Password123!',
        ]);

        $reloginResponse->assertOk();
        $reloginResponse->assertJson([
            'otp_required' => true,
        ]);
        $reloginResponse->assertJsonStructure(['locked_until', 'retry_after_seconds']);

        $this->postJson(route('otp.verify'), [
            'otp_code' => '444444',
        ])->assertStatus(429);

        $this->travel(5)->minutes();

        $this->postJson(route('otp.send'))->assertOk();
    }

    private function makeApprovedUser(string $email = 'otp.user@dswd.gov.ph'): User
    {
        return User::query()->create([
            'name' => 'OTP User',
            'email' => $email,
            'password' => bcrypt('Password123!'),
            'user_id' => strtoupper(str_replace(['@', '.'], '-', $email)),
            'usergroup' => 'user',
            'active' => 1,
            'approvalstatus' => 'A',
        ]);
    }
}