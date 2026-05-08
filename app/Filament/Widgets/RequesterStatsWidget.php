<?php

namespace App\Filament\Widgets;

use App\Models\PurchaseRequest;
use Filament\Widgets\StatsOverviewWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;
use Illuminate\Support\Facades\Auth;

class RequesterStatsWidget extends StatsOverviewWidget
{
    protected function getStats(): array
    {
        $userId = Auth::id();

        return [
            Stat::make('My PR', PurchaseRequest::where('requester_id', $userId)->count())
                ->description('Total requests')
                ->descriptionIcon('heroicon-o-document-text')
                ->color('primary'),

            Stat::make('Pending', PurchaseRequest::where('requester_id', $userId)
                ->whereIn('status', ['draft', 'waiting_approval', 'in_review', 'need_revision'])
                ->count())
                ->description('In progress')
                ->descriptionIcon('heroicon-o-clock')
                ->color('warning'),

            Stat::make('Approved', PurchaseRequest::where('requester_id', $userId)
                ->where('status', 'approved')
                ->count())
                ->description('Approved')
                ->descriptionIcon('heroicon-o-check-circle')
                ->color('success'),
        ];
    }

    protected function getColumns(): int
    {
        return 3;
    }
}
