<?php

namespace Tests\Feature;

use App\Enums\PurchaseRequestStatus;
use App\Models\ApprovalFlow;
use App\Models\ApprovalLevel;
use App\Models\Department;
use App\Models\PurchaseRequest;
use App\Models\User;
use App\Services\PurchaseRequestApprovalService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Notification;
use Tests\TestCase;

class PurchaseRequestApprovalTest extends TestCase
{
    use RefreshDatabase;

    private PurchaseRequestApprovalService $service;
    private Department $department;
    private User $requester;
    private User $sectionHead;
    private User $divisionHead;
    private User $financeAdmin;
    private User $treasurer;

    protected function setUp(): void
    {
        parent::setUp();

        Notification::fake();

        $this->service = app(PurchaseRequestApprovalService::class);

        $this->sectionHead  = User::factory()->create(['role' => 'section_head', 'is_active' => true]);
        $this->divisionHead = User::factory()->create(['role' => 'division_head', 'is_active' => true]);
        $this->financeAdmin = User::factory()->create(['role' => 'finance_admin', 'is_active' => true]);
        $this->treasurer    = User::factory()->create(['role' => 'treasurer', 'is_active' => true]);

        $this->department = Department::factory()->create([
            'section_head_id' => $this->sectionHead->id,
        ]);

        $this->requester = User::factory()->create(['role' => 'requester', 'department_id' => $this->department->id]);

        // Mirror ApprovalFlowSeeder / ApprovalLevelSeeder thresholds.
        $standard = ApprovalFlow::create([
            'name' => 'Standard Approval', 'department_id' => null,
            'min_amount' => 0, 'max_amount' => 10_000_000, 'is_active' => true,
        ]);
        ApprovalLevel::create(['approval_flow_id' => $standard->id, 'level_order' => 1, 'role_type' => ApprovalLevel::ROLE_TYPE_SECTION_HEAD]);

        $management = ApprovalFlow::create([
            'name' => 'Management Approval', 'department_id' => null,
            'min_amount' => 10_000_001, 'max_amount' => 50_000_000, 'is_active' => true,
        ]);
        ApprovalLevel::create(['approval_flow_id' => $management->id, 'level_order' => 1, 'role_type' => ApprovalLevel::ROLE_TYPE_SECTION_HEAD]);
        ApprovalLevel::create(['approval_flow_id' => $management->id, 'level_order' => 2, 'role_type' => ApprovalLevel::ROLE_TYPE_DIVISION_HEAD]);

        $executive = ApprovalFlow::create([
            'name' => 'Executive Approval', 'department_id' => null,
            'min_amount' => 50_000_001, 'max_amount' => 999_999_999_999, 'is_active' => true,
        ]);
        ApprovalLevel::create(['approval_flow_id' => $executive->id, 'level_order' => 1, 'role_type' => ApprovalLevel::ROLE_TYPE_SECTION_HEAD]);
        ApprovalLevel::create(['approval_flow_id' => $executive->id, 'level_order' => 2, 'role_type' => ApprovalLevel::ROLE_TYPE_DIVISION_HEAD]);
        ApprovalLevel::create(['approval_flow_id' => $executive->id, 'level_order' => 3, 'role_type' => ApprovalLevel::ROLE_TYPE_FINANCE_ADMIN]);
        ApprovalLevel::create(['approval_flow_id' => $executive->id, 'level_order' => 4, 'role_type' => ApprovalLevel::ROLE_TYPE_TREASURER]);
    }

    private function makePr(float $amount): PurchaseRequest
    {
        return PurchaseRequest::factory()->create([
            'requester_id' => $this->requester->id,
            'department_id' => $this->department->id,
            'total_amount' => $amount,
        ]);
    }

    public function test_standard_tier_is_approved_after_single_level(): void
    {
        $pr = $this->makePr(5_000_000);

        $approver = $pr->getFirstApprover();
        $this->assertNotNull($approver);
        $this->assertTrue($approver->is($this->sectionHead));

        $this->service->sendForApproval($pr, $approver, $this->requester);
        $pr->refresh();
        $this->assertSame(PurchaseRequestStatus::WAITING_APPROVAL, $pr->status);
        $this->assertSame($this->sectionHead->id, $pr->current_approver_id);

        $this->service->approve($pr, $this->sectionHead);
        $pr->refresh();

        $this->assertSame(PurchaseRequestStatus::APPROVED, $pr->status);
        $this->assertNull($pr->current_approver_id);
    }

    public function test_management_tier_requires_section_then_division_head(): void
    {
        $pr = $this->makePr(30_000_000);

        $approver = $pr->getFirstApprover();
        $this->service->sendForApproval($pr, $approver, $this->requester);
        $pr->refresh();
        $this->assertSame($this->sectionHead->id, $pr->current_approver_id);

        $this->service->approve($pr, $this->sectionHead);
        $pr->refresh();

        // One level down, one to go — must still be waiting, now on division head.
        $this->assertSame(PurchaseRequestStatus::WAITING_APPROVAL, $pr->status);
        $this->assertSame($this->divisionHead->id, $pr->current_approver_id);

        $this->service->approve($pr, $this->divisionHead);
        $pr->refresh();

        $this->assertSame(PurchaseRequestStatus::APPROVED, $pr->status);
    }

    public function test_executive_tier_requires_all_four_levels_in_order(): void
    {
        $pr = $this->makePr(75_000_000);

        $approver = $pr->getFirstApprover();
        $this->service->sendForApproval($pr, $approver, $this->requester);

        $expectedOrder = [$this->sectionHead, $this->divisionHead, $this->financeAdmin, $this->treasurer];

        foreach ($expectedOrder as $expectedApprover) {
            $pr->refresh();
            $this->assertSame($expectedApprover->id, $pr->current_approver_id);
            $this->assertSame(PurchaseRequestStatus::WAITING_APPROVAL, $pr->status);

            $this->service->approve($pr, $expectedApprover);
        }

        $pr->refresh();
        $this->assertSame(PurchaseRequestStatus::APPROVED, $pr->status);
        $this->assertSame($this->treasurer->id, $pr->final_approver_id);
    }

    public function test_a_user_who_is_not_the_current_approver_cannot_approve(): void
    {
        $pr = $this->makePr(75_000_000);
        $approver = $pr->getFirstApprover();
        $this->service->sendForApproval($pr, $approver, $this->requester);

        // treasurer is not first in line for an executive-tier PR yet.
        $this->expectException(\Exception::class);
        $this->service->approve($pr, $this->treasurer);
    }

    public function test_reject_stops_the_chain_regardless_of_level(): void
    {
        $pr = $this->makePr(75_000_000);
        $approver = $pr->getFirstApprover();
        $this->service->sendForApproval($pr, $approver, $this->requester);

        $this->service->reject($pr, $this->sectionHead, 'Anggaran tidak sesuai');
        $pr->refresh();

        $this->assertSame(PurchaseRequestStatus::REJECTED, $pr->status);
        $this->assertNull($pr->current_approver_id);
        $this->assertSame('Anggaran tidak sesuai', $pr->rejection_reason);
    }

    public function test_request_revision_clears_current_approver_and_returns_to_requester(): void
    {
        $pr = $this->makePr(5_000_000);
        $approver = $pr->getFirstApprover();
        $this->service->sendForApproval($pr, $approver, $this->requester);

        $this->service->requestRevision($pr, $this->sectionHead, 'Lampirkan quotation tambahan');
        $pr->refresh();

        $this->assertSame(PurchaseRequestStatus::NEED_REVISION, $pr->status);
        $this->assertNull($pr->current_approver_id);
    }

    public function test_amount_edited_mid_approval_does_not_silently_change_final_approver_immediately(): void
    {
        // Regression guard for the tier-drift issue: sending for approval as Standard
        // must keep routing through the Standard (section-head-only) chain even if
        // total_amount is later bumped — getNextApprover() should have nothing further
        // to add for a flow the PR was never routed into.
        $pr = $this->makePr(5_000_000);
        $approver = $pr->getFirstApprover();
        $this->service->sendForApproval($pr, $approver, $this->requester);

        $pr->update(['total_amount' => 75_000_000]);
        $pr->refresh();

        // Section head still approves as normal for a single-level flow.
        $this->service->approve($pr, $this->sectionHead);
        $pr->refresh();

        $this->assertSame(PurchaseRequestStatus::APPROVED, $pr->status);
    }

    public function test_cancel_and_mark_completed_go_through_service_and_log_history(): void
    {
        $pr = $this->makePr(5_000_000);

        $this->service->cancel($pr, $this->requester, 'Tidak jadi beli');
        $pr->refresh();

        $this->assertSame(PurchaseRequestStatus::CANCELLED, $pr->status);
        $this->assertSame(1, $pr->approvalHistories()->where('action', 'cancelled')->count());

        $pr2 = $this->makePr(5_000_000);
        $pr2->update(['status' => PurchaseRequestStatus::APPROVED]);

        $this->service->markCompleted($pr2, $this->requester);
        $pr2->refresh();

        $this->assertSame(PurchaseRequestStatus::COMPLETED, $pr2->status);
        $this->assertSame(1, $pr2->approvalHistories()->where('action', 'marked_completed')->count());
    }
}
