<?php

namespace Tests\Feature;

use App\Models\ApprovalFlow;
use App\Models\ApprovalLevel;
use App\Models\Department;
use App\Models\PurchaseRequest;
use App\Models\User;
use App\Notifications\PurchaseRequestCreatedNotification;
use App\Notifications\PurchaseRequestSentForApprovalNotification;
use App\Services\PurchaseRequestApprovalService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Notification;
use Tests\TestCase;

class PurchaseRequestEmailFlowTest extends TestCase
{
    use RefreshDatabase;

    private PurchaseRequestApprovalService $service;
    private Department $department;
    private User $admin;
    private User $requester;
    private User $sectionHead;

    protected function setUp(): void
    {
        parent::setUp();

        Notification::fake();

        $this->service = app(PurchaseRequestApprovalService::class);

        $this->admin       = User::factory()->create(['role' => 'admin', 'is_active' => true]);
        $this->sectionHead = User::factory()->create(['role' => 'section_head', 'is_active' => true]);
        $this->department  = Department::factory()->create(['section_head_id' => $this->sectionHead->id]);
        $this->requester   = User::factory()->create(['role' => 'requester', 'department_id' => $this->department->id]);

        $flow = ApprovalFlow::create([
            'name' => 'Standard Approval', 'department_id' => null,
            'min_amount' => 0, 'max_amount' => 10_000_000, 'is_active' => true,
        ]);
        ApprovalLevel::create(['approval_flow_id' => $flow->id, 'level_order' => 1, 'role_type' => ApprovalLevel::ROLE_TYPE_SECTION_HEAD]);
    }

    public function test_creating_a_pr_through_the_service_notifies_admins(): void
    {
        $pr = $this->service->create([
            'department_id' => $this->department->id,
            'request_date'  => now()->toDateString(),
            'required_date' => now()->addWeek()->toDateString(),
            'purpose'       => 'Laptop',
            'total_amount'  => 1_000_000,
        ], $this->requester);

        Notification::assertSentTo($this->admin, PurchaseRequestCreatedNotification::class);
        Notification::assertNotSentTo($this->requester, PurchaseRequestCreatedNotification::class);

        // The mail renders without errors
        $mail = (new PurchaseRequestCreatedNotification($pr))->toMail($this->admin);
        $this->assertStringContainsString($pr->pr_number, $mail->subject);
    }

    public function test_guest_opening_email_link_is_redirected_to_filament_login_and_back(): void
    {
        $pr = $this->sendForApproval();
        $url = $this->approvalUrlFromNotification($pr);

        $this->get($url)
            ->assertRedirect(route('filament.admin.auth.login'));

        // The signed link is remembered so the login response sends the approver back to it
        $intended = session('url.intended');
        $this->assertStringContainsString("/pr/approve/{$pr->id}", $intended);
        $this->assertStringContainsString('signature=', $intended);
        $this->assertStringContainsString("token={$pr->approval_token}", $intended);
    }

    public function test_guest_on_auth_protected_approval_page_is_redirected_to_filament_login(): void
    {
        $pr = $this->sendForApproval();

        $this->get(route('pr.approval.page', ['id' => $pr->id, 'token' => $pr->approval_token]))
            ->assertRedirect(route('filament.admin.auth.login'));
    }

    public function test_approver_can_open_approval_page_from_email_and_sees_the_form(): void
    {
        $pr = $this->sendForApproval();
        $url = $this->approvalUrlFromNotification($pr);

        $this->actingAs($this->sectionHead)
            ->get($url)
            ->assertRedirect(route('pr.approval.page', ['id' => $pr->id, 'token' => $pr->approval_token]));

        $this->actingAs($this->sectionHead)
            ->get(route('pr.approval.page', ['id' => $pr->id, 'token' => $pr->approval_token]))
            ->assertOk()
            ->assertSee('WAITING APPROVAL')
            ->assertSee('Your Decision')
            ->assertSee('Sent For Approval');
    }

    public function test_approver_can_approve_from_the_email_page(): void
    {
        $pr = $this->sendForApproval();

        $this->actingAs($this->sectionHead)
            ->post(route('pr.approval.process', $pr->id), [
                'action' => 'approve',
                'token'  => $pr->approval_token,
            ])
            ->assertRedirect(route('pr.approval.success'));

        $this->assertSame('approved', $pr->fresh()->getStatusValue());
    }

    private function sendForApproval(): PurchaseRequest
    {
        $pr = PurchaseRequest::factory()->create([
            'requester_id'  => $this->requester->id,
            'department_id' => $this->department->id,
            'total_amount'  => 1_000_000,
        ]);

        return $this->service->sendForApproval($pr, $pr->getFirstApprover(), $this->admin);
    }

    private function approvalUrlFromNotification(PurchaseRequest $pr): string
    {
        $url = null;

        Notification::assertSentTo(
            $this->sectionHead,
            PurchaseRequestSentForApprovalNotification::class,
            function (PurchaseRequestSentForApprovalNotification $n) use (&$url) {
                $url = $n->toMail($this->sectionHead)->actionUrl;
                return true;
            }
        );

        return $url;
    }
}
