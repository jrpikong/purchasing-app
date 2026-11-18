<?php

namespace Tests\Feature;

use App\Enums\PurchaseRequestStatus;
use App\Models\ApprovalFlow;
use App\Models\ApprovalLevel;
use App\Models\Department;
use App\Models\PurchaseRequest;
use App\Models\User;
use App\Notifications\PurchaseRequestApprovedNotification;
use App\Notifications\PurchaseRequestProgressNotification as Progress;
use App\Services\PurchaseRequestApprovalService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Notification;
use Tests\TestCase;

class PurchaseRequestProgressNotificationTest extends TestCase
{
    use RefreshDatabase;

    private PurchaseRequestApprovalService $service;
    private Department $department;
    private User $admin;
    private User $pic;
    private User $requester;
    private User $sectionHead;
    private User $divisionHead;

    protected function setUp(): void
    {
        parent::setUp();

        Notification::fake();

        $this->service = app(PurchaseRequestApprovalService::class);

        $this->admin        = User::factory()->create(['role' => 'admin', 'is_active' => true]);
        $this->pic          = User::factory()->create(['role' => 'admin', 'is_active' => true]);
        $this->sectionHead  = User::factory()->create(['role' => 'section_head', 'is_active' => true]);
        $this->divisionHead = User::factory()->create(['role' => 'division_head', 'is_active' => true]);
        $this->department   = Department::factory()->create(['section_head_id' => $this->sectionHead->id]);
        $this->requester    = User::factory()->create(['role' => 'requester', 'department_id' => $this->department->id]);

        $flow = ApprovalFlow::create([
            'name' => 'Management Approval', 'department_id' => null,
            'min_amount' => 0, 'max_amount' => 50_000_000, 'is_active' => true,
        ]);
        ApprovalLevel::create(['approval_flow_id' => $flow->id, 'level_order' => 1, 'role_type' => ApprovalLevel::ROLE_TYPE_SECTION_HEAD]);
        ApprovalLevel::create(['approval_flow_id' => $flow->id, 'level_order' => 2, 'role_type' => ApprovalLevel::ROLE_TYPE_DIVISION_HEAD]);
    }

    public function test_requester_is_told_when_pr_is_sent_and_when_a_level_is_approved(): void
    {
        $pr = $this->sentPr();

        $this->assertProgressSent($this->requester, Progress::SENT_FOR_APPROVAL);

        $this->service->approve($pr, $this->sectionHead);

        $this->assertProgressSent($this->requester, Progress::LEVEL_APPROVED, fn ($n) =>
            $n->context['next_approver'] === $this->divisionHead->name && $n->context['level'] === 1
        );
    }

    public function test_pic_is_told_when_pr_is_fully_approved(): void
    {
        $pr = $this->sentPr(withPic: true);

        $this->service->approve($pr, $this->sectionHead);
        $this->service->approve($pr->fresh(), $this->divisionHead);

        Notification::assertSentTo($this->requester, PurchaseRequestApprovedNotification::class);
        $this->assertProgressSent($this->pic, Progress::APPROVED);
        $this->assertProgressNotSent($this->admin, Progress::APPROVED); // PIC takes precedence over the sender
    }

    public function test_sender_admin_is_told_when_there_is_no_pic(): void
    {
        $pr = $this->sentPr();

        $this->service->reject($pr, $this->sectionHead, 'Anggaran habis');

        $this->assertProgressSent($this->admin, Progress::REJECTED, fn ($n) => $n->context['note'] === 'Anggaran habis');
    }

    public function test_revision_then_resubmit_then_send_again(): void
    {
        $pr = $this->sentPr(withPic: true);

        $this->service->requestRevision($pr, $this->sectionHead, 'Tambah quotation');
        $this->assertProgressSent($this->pic, Progress::REVISION_REQUESTED);

        $this->service->resubmit($pr->fresh(), $this->requester, 'Quotation sudah ditambah');
        $pr->refresh();

        $this->assertSame(PurchaseRequestStatus::IN_REVIEW, $pr->status);
        $this->assertSame(1, $pr->approvalHistories()->where('action', 'resubmitted')->count());
        $this->assertProgressSent($this->pic, Progress::RESUBMITTED, fn ($n) => $n->context['note'] === 'Quotation sudah ditambah');

        // Admin can now send it for approval again
        $this->service->sendForApproval($pr, $pr->getFirstApprover(), $this->admin);
        $this->assertSame(PurchaseRequestStatus::WAITING_APPROVAL, $pr->fresh()->status);
    }

    public function test_resubmit_notifies_all_admins_when_nobody_handled_it_yet(): void
    {
        $pr = $this->makePr(['status' => PurchaseRequestStatus::NEED_REVISION]);

        $this->service->resubmit($pr, $this->requester);

        $this->assertProgressSent($this->admin, Progress::RESUBMITTED);
        $this->assertProgressSent($this->pic, Progress::RESUBMITTED);
        $this->assertProgressNotSent($this->requester, Progress::RESUBMITTED);
    }

    public function test_only_requester_can_resubmit_and_only_from_need_revision(): void
    {
        $pr = $this->makePr(['status' => PurchaseRequestStatus::NEED_REVISION]);

        $this->assertTrue($this->requester->can('resubmit', $pr));
        $this->assertFalse($this->admin->can('resubmit', $pr));

        try {
            $this->service->resubmit($pr, $this->admin);
            $this->fail('Non-requester must not be able to resubmit.');
        } catch (\Exception) {
        }

        $draft = $this->makePr();
        $this->assertFalse($this->requester->can('resubmit', $draft));
        $this->expectException(\Exception::class);
        $this->service->resubmit($draft, $this->requester);
    }

    public function test_cancel_notifies_requester_pic_and_pending_approver_but_not_the_actor(): void
    {
        $pr = $this->sentPr(withPic: true);

        $this->service->cancel($pr, $this->admin, 'Duplikat');

        $this->assertProgressSent($this->requester, Progress::CANCELLED);
        $this->assertProgressSent($this->pic, Progress::CANCELLED);
        $this->assertProgressSent($this->sectionHead, Progress::CANCELLED);
        $this->assertProgressNotSent($this->admin, Progress::CANCELLED);
    }

    public function test_requester_is_told_when_pr_is_completed(): void
    {
        $pr = $this->makePr(['status' => PurchaseRequestStatus::APPROVED]);

        $this->service->markCompleted($pr, $this->admin);

        $this->assertProgressSent($this->requester, Progress::COMPLETED);
    }

    public function test_every_progress_mail_renders(): void
    {
        $pr = $this->makePr();
        $events = [
            Progress::SENT_FOR_APPROVAL, Progress::LEVEL_APPROVED, Progress::APPROVED, Progress::REJECTED,
            Progress::REVISION_REQUESTED, Progress::RESUBMITTED, Progress::CANCELLED, Progress::COMPLETED,
        ];

        foreach ($events as $event) {
            $n = new Progress($pr, $event, ['actor' => 'A', 'next_approver' => 'B', 'level' => 1, 'note' => 'N']);
            $mail = $n->toMail($this->requester);

            $this->assertStringContainsString($pr->pr_number, $mail->subject, $event);
            $this->assertNotEmpty((string) $mail->render(), $event);
            $this->assertSame($event, $n->toArray($this->requester)['action']);
        }
    }

    private function makePr(array $attributes = []): PurchaseRequest
    {
        return PurchaseRequest::factory()->create([
            'requester_id'  => $this->requester->id,
            'department_id' => $this->department->id,
            'total_amount'  => 20_000_000,
            ...$attributes,
        ]);
    }

    private function sentPr(bool $withPic = false): PurchaseRequest
    {
        $pr = $this->makePr();

        if ($withPic) {
            $this->service->assignPic($pr, $this->pic, $this->admin);
        }

        return $this->service->sendForApproval($pr->fresh(), $pr->getFirstApprover(), $this->admin);
    }

    private function assertProgressSent(User $user, string $event, ?callable $extra = null): void
    {
        Notification::assertSentTo($user, Progress::class, fn (Progress $n) =>
            $n->event === $event && ($extra === null || $extra($n))
        );
    }

    private function assertProgressNotSent(User $user, string $event): void
    {
        Notification::assertNotSentTo($user, Progress::class, fn (Progress $n) => $n->event === $event);
    }
}
