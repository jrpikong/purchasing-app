<?php

namespace Tests\Feature;

use App\Filament\Resources\EmailLogs\EmailLogResource;
use App\Filament\Resources\EmailLogs\Pages\ListEmailLogs;
use App\Models\Department;
use App\Models\EmailLog;
use App\Models\PurchaseRequest;
use App\Models\User;
use Filament\Facades\Filament;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Tests\TestCase;

class EmailLogResourceTest extends TestCase
{
    use RefreshDatabase;

    private User $admin;
    private User $requester;
    private PurchaseRequest $pr;

    protected function setUp(): void
    {
        parent::setUp();

        Filament::setCurrentPanel(Filament::getPanel('admin'));

        $department = Department::factory()->create();
        $this->admin = User::factory()->create(['role' => 'admin', 'is_active' => true]);
        $this->requester = User::factory()->create(['role' => 'requester', 'department_id' => $department->id]);
        $this->pr = PurchaseRequest::factory()->create([
            'requester_id' => $this->requester->id,
            'department_id' => $department->id,
        ]);
    }

    public function test_panel_is_reachable_in_production_for_active_users_only(): void
    {
        config(['app.env' => 'production']);

        $this->actingAs($this->admin)
            ->get(EmailLogResource::getUrl('index'))
            ->assertOk();

        $inactive = User::factory()->create(['role' => 'admin', 'is_active' => false]);
        $this->actingAs($inactive)
            ->get(EmailLogResource::getUrl('index'))
            ->assertForbidden();

        $expired = User::factory()->create(['role' => 'admin', 'membership_expired_at' => now()->subDay()]);
        $this->actingAs($expired)
            ->get(EmailLogResource::getUrl('index'))
            ->assertForbidden();
    }

    public function test_admin_sees_email_logs_and_non_admin_does_not(): void
    {
        $sent = $this->log(['is_sent' => true, 'sent_at' => now(), 'email_subject' => 'Approval required: PR-SENT']);

        $this->actingAs($this->admin)
            ->get(EmailLogResource::getUrl('index'))
            ->assertOk()
            ->assertSee('Approval required: PR-SENT')
            ->assertSee('Terkirim');

        $this->actingAs($this->requester)
            ->get(EmailLogResource::getUrl('index'))
            ->assertForbidden();

        $this->actingAs($this->requester)
            ->get(EmailLogResource::getUrl('view', ['record' => $sent]))
            ->assertForbidden();
    }

    public function test_status_tabs_filter_records(): void
    {
        $sent = $this->log(['is_sent' => true, 'sent_at' => now()]);
        $failed = $this->log(['is_sent' => false, 'error_message' => 'Connection could not be established']);
        $pending = $this->log(['is_sent' => false]);

        $this->actingAs($this->admin);

        Livewire::test(ListEmailLogs::class)
            ->assertCanSeeTableRecords([$sent, $failed, $pending])
            ->set('activeTab', EmailLog::STATUS_FAILED)
            ->assertCanSeeTableRecords([$failed])
            ->assertCanNotSeeTableRecords([$sent, $pending])
            ->set('activeTab', EmailLog::STATUS_PENDING)
            ->assertCanSeeTableRecords([$pending])
            ->assertCanNotSeeTableRecords([$sent, $failed])
            ->set('activeTab', EmailLog::STATUS_SENT)
            ->assertCanSeeTableRecords([$sent])
            ->assertCanNotSeeTableRecords([$failed, $pending]);

        $this->assertSame('1', EmailLogResource::getNavigationBadge());
    }

    public function test_search_matches_recipient_name_and_address(): void
    {
        $other = User::factory()->create(['name' => 'Budi Santoso', 'email' => 'budi@example.org']);
        $mine = $this->log();
        $budi = $this->log(['recipient_id' => $other->id, 'email_to' => 'budi@example.org']);

        $this->actingAs($this->admin);

        Livewire::test(ListEmailLogs::class)
            ->searchTable('Budi')
            ->assertCanSeeTableRecords([$budi])
            ->assertCanNotSeeTableRecords([$mine])
            ->searchTable('budi@example')
            ->assertCanSeeTableRecords([$budi])
            ->assertCanNotSeeTableRecords([$mine]);
    }

    public function test_view_page_shows_delivery_error(): void
    {
        $failed = $this->log([
            'email_type' => 'progress_approval_stalled',
            'error_message' => 'SMTP 535 authentication failed',
            'retry_count' => 2,
        ]);

        $this->actingAs($this->admin)
            ->get(EmailLogResource::getUrl('view', ['record' => $failed]))
            ->assertOk()
            ->assertSee('SMTP 535 authentication failed')
            ->assertSee('Eskalasi: Approval Macet')
            ->assertSee($this->pr->pr_number);
    }

    public function test_log_is_read_only(): void
    {
        $this->actingAs($this->admin);

        $this->assertFalse(EmailLogResource::canCreate());
        $this->assertFalse(EmailLogResource::canEdit($this->log()));
        $this->assertFalse(EmailLogResource::canDelete($this->log()));
    }

    private function log(array $attributes = []): EmailLog
    {
        return EmailLog::create([
            'purchase_request_id' => $this->pr->id,
            'recipient_id' => $this->requester->id,
            'notification_id' => (string) \Illuminate\Support\Str::uuid(),
            'email_type' => EmailLog::TYPE_APPROVAL_REQUEST,
            'email_to' => $this->requester->email,
            'email_subject' => 'Approval required: ' . $this->pr->pr_number,
            'is_sent' => false,
            ...$attributes,
        ]);
    }
}
