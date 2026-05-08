<?php

namespace App\Filament\Pages;

use App\Filament\Widgets\RequesterStatsWidget;
use App\Models\PurchaseRequest;
use Filament\Pages\Page;
use Filament\Support\Facades\FilamentView;
use Illuminate\Support\Facades\Auth;

class RequesterDashboard extends Page
{
    protected static string|null|\BackedEnum $navigationIcon = 'heroicon-o-document-plus';

    protected static ?string $navigationLabel = 'Dashboard Requester';

    protected static ?int $navigationSort = 2;

    protected static string|null|\UnitEnum $navigationGroup = 'Dashboard';
    protected static bool $shouldRegisterNavigation = false;

    protected static ?string $slug = 'requester-dashboard';

    public function getTitle(): string
    {
        return 'My Dashboard';
    }

    protected function getHeaderWidgets(): array
    {
        return [
            RequesterStatsWidget::class,
        ];
    }

    public function getViewData(): array
    {
        $userId = Auth::id();

        return [
            'recentPrs' => PurchaseRequest::where('requester_id', $userId)
                ->with(['department', 'currentApprover'])
                ->latest()
                ->limit(5)
                ->get(),
        ];
    }

    public static function canView(): bool
    {
        return FilamentView::auth()->user()->hasRole(['requester', 'admin', 'super_admin']);
    }

    public function getView(): string
    {
        return 'filament.pages.requester-dashboard';
    }
}
