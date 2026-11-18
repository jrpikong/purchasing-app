<?php

namespace Tests\Feature;

use App\Enums\PurchaseRequestStatus;
use App\Models\ApprovalFlow;
use App\Models\ApprovalLevel;
use App\Models\Department;
use App\Models\EmailLog;
use App\Models\PurchaseRequest;
use App\Models\User;
use App\Notifications\PurchaseRequestCreatedNotification;
use App\Notifications\PurchaseRequestProgressNotification as Progress;
use App\Notifications\PurchaseRequestRevisionNotification;
use App\Notifications\PurchaseRequestSentForApprovalNotification;
use App\Services\PurchaseRequestApprovalService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Notification;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

/**
 * Points 7 and 11–14 of the PR flow review.
 */
class PurchaseRequestHardeningTest extends TestCase
{
    use RefreshDatabase;

    private PurchaseRequestApprovalService $service;
    private Department $department;
    private User $admin;
    private User $requester;
    private User $sectionHead;
    private User $divisionHead;

    protected function setUp(): void
    {
        parent::setUp();

        $this->service = app(PurchaseRequestApprovalService::class);

        $this->admin        = User::factory()->create(['role' => 'admin', 'is_active' => true]);
        $this->sectionHead  = User::factory()->create(['role' => 'section_head', 'is_active' => true]);
        $this->divisionHead = User::factory()->create(['role' => 'division_head', 'is_active' => true]);
        $this->department   = Department::factory()->create(['section_head_id' => $this->sectionHead->id]);
        $this->requester    = User::factory()->create(['role' => 'requester', 'department_id' => $this->department->id]);

        $standard = ApprovalFlow::create([
            'name' => 'Standard', 'department_id' => null,
            'min_amount' => 0, 'max_amount' => 10_000_000, 'is_active' => true,
        ]);
        ApprovalLevel::create(['approval_flow_id' => $standard->id, 'level_order' => 1, 'role_type' => ApprovalLevel::ROLE_TYPE_SECTION_HEAD]);

        $management = ApprovalFlow::create([
            'name' => 'Management', 'department_id' => null,
            'min_amount' => 10_000_001, 'max_amount' => 50_000_000, 'is_active' => true,
        ]);
        ApprovalLevel::create(['approval_flow_id' => $management->id, 'level_order' => 1, 'role_type' => ApprovalLevel::ROLE_TYPE_SECTION_HEAD]);
        ApprovalLevel::create(['approval_flow_id' => $management->id, 'level_order' => 2, 'role_type' => ApprovalLevel::ROLE_TYPE_DIVISION_HEAD]);
    }

    // ── 7. No self-approval ──────────────────────────────────────────────

    public function test_level_held_by_the_requester_is_skipped_and_logged(): void
    {
        Notification::fake();

        $pr = $this->makePr(20_000_000, requester: $this->sectionHead);
        $pr = $this->service->sendForApproval($pr, null, $this->admin);

        $this->assertSame($this->divisionHead->id, $pr->current_approver_id);
        $this->assertSame(2, $pr->current_level_order);
        $this->assertSame(1, $pr->approvalHistories()->where('action', 'level_skipped')->count());
    }

    public function test_flow_whose_only_approver_is_the_requester_cannot_be_sent(): void
    {
        Notification::fake();

        $pr = $this->makePr(5_000_000, requester: $this->sectionHead);

        try {
            $this->service->sendForApproval($pr, null, $this->admin);
            $this->fail('Sending should be refused when only the requester could approve.');
        } catch (\Exception $e) {
            $this->assertStringContainsString('requester sendiri', $e->getMessage());
        }

        $this->assertSame(PurchaseRequestStatus::DRAFT, $pr->fresh()->status);
    }

    public function test_requester_cannot_decide_on_own_pr_even_if_set_as_current_approver(): void
    {
        Notification::fake();

        $pr = $this->makePr(5_000_000);
        $pr->update(['status' => PurchaseRequestStatus::WAITING_APPROVAL, 'current_approver_id' => $this->requester->id]);
        $pr->refresh();

        $this->assertFalse($this->requester->can('approve', $pr));
        $this->assertFalse($this->requester->can('reject', $pr));
        $this->assertFalse($this->requester->can('requestRevision', $pr));

        $this->expectExceptionMessage('miliknya sendiri');
        $this->service->approve($pr, $this->requester);
    }

    public function test_any_current_approver_may_decide_regardless_of_role(): void
    {
        Notification::fake();

        // "specific user" level held by someone with the generic approver role
        $specific = User::factory()->create(['role' => 'approver', 'is_active' => true]);
        $flow = ApprovalFlow::create([
            'name' => 'Specific', 'department_id' => $this->department->id,
            'min_amount' => 0, 'max_amount' => 10_000_000, 'is_active' => true,
        ]);
        ApprovalLevel::create(['approval_flow_id' => $flow->id, 'level_order' => 1, 'role_type' => ApprovalLevel::ROLE_TYPE_SPECIFIC_USER, 'approver_id' => $specific->id]);

        $pr = $this->service->sendForApproval($this->makePr(5_000_000), null, $this->admin);

        $this->assertSame($specific->id, $pr->current_approver_id);
        $this->assertTrue($specific->can('approve', $pr));
    }

    // ── 11. Revision email carries only this round's notes ──────────────

    public function test_revision_email_shows_only_the_latest_notes(): void
    {
        Notification::fake();

        $pr = $this->makePr(5_000_000, ['notes' => "Cancelled: lama\n\nRevision requested by X: catatan lama"]);
        $pr = $this->service->sendForApproval($pr, null, $this->admin);
        $this->service->requestRevision($pr, $this->sectionHead, 'Catatan terbaru');

        Notification::assertSentTo($this->requester, PurchaseRequestRevisionNotification::class, function ($n) {
            $body = implode("\n", $n->toMail($this->requester)->introLines);

            return $n->revisionNotes === 'Catatan terbaru'
                && str_contains($body, 'Catatan terbaru')
                && ! str_contains($body, 'catatan lama')
                && str_contains($body, $this->sectionHead->name);
        });
    }

    // ── 12. Reminders, fresh links, escalation ──────────────────────────

    public function test_admin_can_resend_link_which_invalidates_the_old_one(): void
    {
        Notification::fake();

        $pr = $this->service->sendForApproval($this->makePr(5_000_000), null, $this->admin);
        $oldToken = $pr->approval_token;

        $this->assertTrue($this->admin->can('resendApprovalLink', $pr));
        $this->assertFalse($this->requester->can('resendApprovalLink', $pr));

        $pr = $this->service->resendApprovalLink($pr, $this->admin);

        $this->assertNotSame($oldToken, $pr->approval_token);
        $this->assertFalse($this->service->validateToken($pr, $oldToken));
        $this->assertTrue($this->service->validateToken($pr, $pr->approval_token));
        Notification::assertSentToTimes($this->sectionHead, PurchaseRequestSentForApprovalNotification::class, 2);
    }

    public function test_overdue_pr_gets_rate_limited_reminders_then_one_escalation(): void
    {
        Notification::fake();

        $pr = $this->makePr(5_000_000);
        $pr = $this->service->sendForApproval($pr, null, $this->admin, now()->subDay()); // already overdue

        // 3 reminders, one per day — a second run on the same day does nothing
        for ($day = 1; $day <= PurchaseRequestApprovalService::MAX_REMINDERS; $day++) {
            $this->artisan('approvals:check-overdue')->assertSuccessful();
            $this->artisan('approvals:check-overdue')->assertSuccessful();
            $this->assertSame($day, $pr->fresh()->reminder_count);
            $this->travel(1)->days();
        }

        Notification::assertSentTo($this->sectionHead, PurchaseRequestSentForApprovalNotification::class,
            fn ($n) => $n->isReminder);
        Notification::assertSentToTimes($this->sectionHead, PurchaseRequestSentForApprovalNotification::class,
            1 + PurchaseRequestApprovalService::MAX_REMINDERS);

        // Next run escalates to the admin who sent it — once
        $this->artisan('approvals:check-overdue')->assertSuccessful();
        $this->travel(1)->days();
        $this->artisan('approvals:check-overdue')->assertSuccessful();

        Notification::assertSentToTimes($this->admin, Progress::class, 1);
        Notification::assertSentTo($this->admin, Progress::class, fn ($n) => $n->event === Progress::APPROVAL_STALLED);
        Notification::assertSentToTimes($this->sectionHead, PurchaseRequestSentForApprovalNotification::class,
            1 + PurchaseRequestApprovalService::MAX_REMINDERS);
    }

    public function test_pending_pr_without_deadline_is_reminded_after_48_hours_only(): void
    {
        Notification::fake();

        $this->service->sendForApproval($this->makePr(5_000_000), null, $this->admin);

        $this->travel(47)->hours();
        $this->artisan('approvals:check-overdue')->assertSuccessful();
        Notification::assertSentToTimes($this->sectionHead, PurchaseRequestSentForApprovalNotification::class, 1);

        $this->travel(2)->hours();
        $this->artisan('approvals:check-overdue')->assertSuccessful();
        Notification::assertSentToTimes($this->sectionHead, PurchaseRequestSentForApprovalNotification::class, 2);
    }

    public function test_reminder_counter_resets_when_pr_moves_to_next_level(): void
    {
        Notification::fake();

        $pr = $this->service->sendForApproval($this->makePr(20_000_000), null, $this->admin, now()->subDay());
        $this->artisan('approvals:check-overdue');
        $this->assertSame(1, $pr->fresh()->reminder_count);

        $pr = $this->service->approve($pr->fresh(), $this->sectionHead);

        $this->assertSame(0, $pr->reminder_count);
        $this->assertNull($pr->last_reminded_at);
        $this->assertNotNull($pr->current_level_started_at);
    }

    // ── 13. One definition of "admin" / role ───────────────────────────

    public function test_spatie_role_is_the_source_of_truth_over_the_admin_default_column(): void
    {
        Role::findOrCreate('treasurer');

        // Created like the Filament user form does: column left at its old 'admin' default
        $treasurer = User::factory()->create(['role' => 'admin', 'is_active' => true]);
        $treasurer->assignRole('treasurer');
        $treasurer->refresh();

        $this->assertFalse($treasurer->isAdmin());
        $this->assertTrue($treasurer->hasAppRole('treasurer'));
        $this->assertFalse(User::query()->admins()->whereKey($treasurer->id)->exists());

        $level = new ApprovalLevel(['role_type' => ApprovalLevel::ROLE_TYPE_TREASURER]);
        $this->assertTrue($level->getApprover()?->is($treasurer));

        $treasurer->syncRoleColumnFromSpatie();
        $this->assertSame('treasurer', $treasurer->fresh()->role->value);
    }

    public function test_spatie_only_admin_gets_new_pr_email_and_admin_rights(): void
    {
        Notification::fake();
        Role::findOrCreate('admin');

        $spatieAdmin = User::factory()->create(['role' => 'requester', 'is_active' => true]);
        $spatieAdmin->assignRole('admin');

        $this->service->create([
            'department_id' => $this->department->id,
            'required_date' => now()->addWeek()->toDateString(),
            'purpose' => 'Printer',
            'total_amount' => 1_000_000,
        ], $this->requester);

        Notification::assertSentTo($spatieAdmin, PurchaseRequestCreatedNotification::class);
        $this->assertTrue($spatieAdmin->fresh()->can('assign', PurchaseRequest::first()));
    }

    public function test_inactive_admins_get_no_admin_emails(): void
    {
        Notification::fake();

        $inactive = User::factory()->create(['role' => 'admin', 'is_active' => false]);

        $this->service->create([
            'department_id' => $this->department->id,
            'required_date' => now()->addWeek()->toDateString(),
            'purpose' => 'Kursi',
            'total_amount' => 1_000_000,
        ], $this->requester);

        Notification::assertSentTo($this->admin, PurchaseRequestCreatedNotification::class);
        Notification::assertNotSentTo($inactive, PurchaseRequestCreatedNotification::class);
    }

    // ── 14. email_logs audit trail ──────────────────────────────────────

    public function test_delivered_emails_are_logged_as_sent(): void
    {
        // Real notification pipeline: phpunit.xml uses the array mailer + sync queue
        $pr = $this->service->sendForApproval($this->makePr(5_000_000), null, $this->admin);

        $log = EmailLog::where('purchase_request_id', $pr->id)
            ->where('recipient_id', $this->sectionHead->id)
            ->sole();

        $this->assertTrue($log->is_sent);
        $this->assertNotNull($log->sent_at);
        $this->assertSame(EmailLog::TYPE_APPROVAL_REQUEST, $log->email_type);
        $this->assertSame($this->sectionHead->email, $log->email_to);
        $this->assertStringContainsString($pr->pr_number, $log->email_subject);
        $this->assertNotNull($log->notification_id);

        // Requester's "sent for approval" progress email is logged too
        $this->assertTrue(EmailLog::where('recipient_id', $this->requester->id)
            ->where('email_type', 'progress_' . Progress::SENT_FOR_APPROVAL)
            ->where('is_sent', true)
            ->exists());
    }

    public function test_failed_queued_email_records_the_error(): void
    {
        $pr = $this->makePr(5_000_000);
        $notification = new Progress($pr, Progress::COMPLETED, ['actor' => 'A']);
        $notification->id = (string) \Illuminate\Support\Str::uuid();

        EmailLog::create([
            'purchase_request_id' => $pr->id,
            'recipient_id' => $this->requester->id,
            'notification_id' => $notification->id,
            'email_type' => 'progress_completed',
            'email_to' => $this->requester->email,
            'email_subject' => 'x',
            'is_sent' => false,
        ]);

        // What Laravel calls after the queued job has used up its retries
        $notification->failed(new \RuntimeException('SMTP 535 authentication failed'));

        $log = EmailLog::where('notification_id', $notification->id)->sole();
        $this->assertFalse($log->is_sent);
        $this->assertSame('SMTP 535 authentication failed', $log->error_message);
    }

    private function makePr(float $amount, array $attributes = [], ?User $requester = null): PurchaseRequest
    {
        $requester ??= $this->requester;

        return PurchaseRequest::factory()->create([
            'requester_id' => $requester->id,
            'department_id' => $this->department->id,
            'total_amount' => $amount,
            ...$attributes,
        ]);
    }
}
