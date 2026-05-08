<?php

namespace App\Filament\Pages;

use App\Filament\Widgets\PendingApprovalsStatsWidget;
use App\Models\ApprovalHistory;
use App\Models\PurchaseRequest;
use Filament\Pages\Page;
use Filament\Support\Facades\FilamentView;
use Illuminate\Support\Facades\Auth;

class ApproverDashboard extends Page
{
    protected static string|null|\BackedEnum $navigationIcon = 'heroicon-o-check-circle';

    protected static ?string $navigationLabel = 'Dashboard Approver';

    protected static ?int $navigationSort = 1;

    protected static string|null|\UnitEnum $navigationGroup = 'Dashboard';

    protected static ?string $slug = 'approver-dashboard';

    protected static bool $shouldRegisterNavigation = false;

    public function getTitle(): string
    {
        return 'Approver Dashboard';
    }

    protected function getHeaderWidgets(): array
    {
        return [
            PendingApprovalsStatsWidget::class,
        ];
    }

    protected function getFooterWidgets(): array
    {
        return [];
    }

    public function getViewData(): array
    {
        $userId = Auth::id();

        $avgResponseTime = ApprovalHistory::where('actor_id', $userId)
            ->whereMonth('acted_at', now()->month)
            ->whereYear('acted_at', now()->year)
            ->selectRaw('AVG(TIMESTAMPDIFF(HOUR, created_at, acted_at)) as avg_time')
            ->value('avg_time') ?? 0;

        $pendingApprovals = PurchaseRequest::query()
            ->where('current_approver_id', $userId)
            ->whereIn('status', ['waiting_approval', 'in_review'])
            ->with(['requester', 'department'])
            ->latest()
            ->limit(5)
            ->get();

        return [
            'pendingCount' => PurchaseRequest::where('current_approver_id', $userId)
                ->whereIn('status', ['waiting_approval', 'in_review'])
                ->count(),
            'approvedCount' => PurchaseRequest::where('final_approver_id', $userId)
                ->where('status', 'approved')
                ->count(),
            'thisMonthCount' => PurchaseRequest::whereMonth('created_at', now()->month)
                ->whereYear('created_at', now()->year)
                ->where('requester_id', '!=', $userId)
                ->whereHas('approvalHistories', fn($q) => $q->where('actor_id', $userId))
                ->count(),
            'avgResponseTime' => $avgResponseTime,
            'pendingApprovals' => $pendingApprovals,
        ];
    }

    public static function canView(): bool
    {
        $user = FilamentView::auth()->user();
        return $user->hasRole(['section_head', 'division_head', 'finance_admin', 'treasurer', 'admin', 'super_admin']);
    }

    public function getView(): string
    {
        return 'filament.pages.approver-dashboard';
    }
}
