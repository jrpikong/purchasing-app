<?php

namespace App\Filament\Widgets;

use App\Models\PurchaseRequest;
use Filament\Widgets\StatsOverviewWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;
use Illuminate\Support\Facades\Auth;

class PendingApprovalsStatsWidget extends StatsOverviewWidget
{
    protected function getStats(): array
    {
        return [
            Stat::make('Pending Approvals', PurchaseRequest::where('current_approver_id', Auth::id())
                ->whereIn('status', ['waiting_approval', 'in_review'])
                ->count())
                ->description('Needs your approval')
                ->descriptionIcon('heroicon-o-clock')
                ->color('warning'),
        ];
    }

    protected function getColumns(): int
    {
        return 1;
    }
}
