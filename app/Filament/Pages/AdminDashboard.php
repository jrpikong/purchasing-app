<?php

namespace App\Filament\Pages;

use App\Filament\Widgets\PrStatsWidget;
use Filament\Pages\Page;
use Filament\Support\Facades\FilamentView;

class AdminDashboard extends Page
{
    protected static string|null|\BackedEnum $navigationIcon = 'heroicon-o-shield-check';

    protected static ?string $navigationLabel = 'Dashboard Admin';

    protected static ?int $navigationSort = 0;

    protected static string|null|\UnitEnum $navigationGroup = 'Dashboard';

    protected static ?string $slug = 'admin-dashboard';

    protected static bool $shouldRegisterNavigation = false;

    public function getTitle(): string
    {
        return 'Admin Dashboard';
    }

    protected function getHeaderWidgets(): array
    {
        return [
            PrStatsWidget::class,
        ];
    }

    protected function getFooterWidgets(): array
    {
        return [];
    }

    public static function canView(): bool
    {
        return FilamentView::auth()->user()->hasRole(['admin', 'super_admin']);
    }

    public function getView(): string
    {
        return 'filament.pages.admin-dashboard';
    }
}
