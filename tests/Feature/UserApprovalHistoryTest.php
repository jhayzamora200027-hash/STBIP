<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Schema;
use Tests\TestCase;

class UserApprovalHistoryTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();

        Schema::dropIfExists('approval_histories');
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
            $table->string('approvalstatus')->nullable();
            $table->string('approvalcomment')->nullable();
            $table->string('approvedby')->nullable();
            $table->string('phonenumber')->nullable();
            $table->string('gender')->nullable();
            $table->text('address')->nullable();
            $table->rememberToken();
            $table->timestamps();
        });

        Schema::create('approval_histories', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->nullable()->constrained('users')->nullOnDelete();
            $table->string('applicant_name');
            $table->string('applicant_email');
            $table->string('action');
            $table->string('reviewed_by_name')->nullable();
            $table->string('reviewed_by_email')->nullable();
            $table->string('assigned_usergroup')->nullable();
            $table->text('rejection_reason')->nullable();
            $table->timestamps();
        });

        Mail::fake();
    }

    protected function tearDown(): void
    {
        Schema::dropIfExists('approval_histories');
        Schema::dropIfExists('users');

        parent::tearDown();
    }

    public function test_approval_and_rejection_actions_create_history_logs(): void
    {
        $admin = $this->makeUser('admin', 'admin@dswd.gov.ph', 'Admin Reviewer');
        $approvedApplicant = $this->makeApplicant('approved.user@dswd.gov.ph', 'Approved User');
        $rejectedApplicant = $this->makeApplicant('rejected.user@dswd.gov.ph', 'Rejected User');

        $this->actingAs($admin)->putJson(route('users.approval.update', $approvedApplicant->id), [
            'approval_status' => 'A',
            'usergroup' => 'user',
        ])->assertOk();

        $this->actingAs($admin)->putJson(route('users.approval.update', $rejectedApplicant->id), [
            'approval_status' => 'R',
            'approvalcomment' => 'Incomplete registration details.',
        ])->assertOk();

        $this->assertDatabaseHas('approval_histories', [
            'user_id' => $approvedApplicant->id,
            'applicant_email' => 'approved.user@dswd.gov.ph',
            'action' => 'approved',
            'reviewed_by_name' => 'Admin Reviewer',
            'reviewed_by_email' => 'admin@dswd.gov.ph',
            'assigned_usergroup' => 'user',
        ]);

        $this->assertDatabaseHas('approval_histories', [
            'user_id' => $rejectedApplicant->id,
            'applicant_email' => 'rejected.user@dswd.gov.ph',
            'action' => 'rejected',
            'rejection_reason' => 'Incomplete registration details.',
            'reviewed_by_name' => 'Admin Reviewer',
        ]);
    }

    public function test_approval_history_filters_by_email(): void
    {
        $admin = $this->makeUser('admin', 'admin@dswd.gov.ph', 'Admin Reviewer');
        $targetApplicant = $this->makeApplicant('target.user@dswd.gov.ph', 'Target User');
        $otherApplicant = $this->makeApplicant('other.user@dswd.gov.ph', 'Other User');

        $this->actingAs($admin)->putJson(route('users.approval.update', $targetApplicant->id), [
            'approval_status' => 'A',
            'usergroup' => 'user',
        ])->assertOk();

        $this->actingAs($admin)->putJson(route('users.approval.update', $otherApplicant->id), [
            'approval_status' => 'R',
            'approvalcomment' => 'Rejected for testing.',
        ])->assertOk();

        $response = $this->actingAs($admin)->get(route('approvals.index', [
            'history_email' => 'target.user@dswd.gov.ph',
        ]));

        $response->assertOk();
        $response->assertSee('target.user@dswd.gov.ph');
        $response->assertDontSee('other.user@dswd.gov.ph');
    }

    private function makeUser(string $role, string $email, string $name): User
    {
        return User::query()->create([
            'name' => $name,
            'email' => $email,
            'usergroup' => $role,
            'approvalstatus' => 'A',
            'password' => bcrypt('password'),
        ]);
    }

    private function makeApplicant(string $email, string $name): User
    {
        return User::query()->create([
            'name' => $name,
            'email' => $email,
            'user_id' => strtoupper(str_replace(['@', '.'], '-', $email)),
            'password' => bcrypt('password'),
            'approvalstatus' => null,
            'phonenumber' => '09123456789',
            'gender' => 'other',
            'address' => 'Test Address',
            'created_at' => now(),
            'updated_at' => now(),
        ]);
    }
}