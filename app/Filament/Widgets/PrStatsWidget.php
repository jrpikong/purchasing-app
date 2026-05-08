<?php

namespace App\Filament\Widgets;

use Filament\Widgets\StatsOverviewWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;
use Illuminate\Support\Facades\DB;

class PrStatsWidget extends StatsOverviewWidget
{
    protected function getStats(): array
    {
        return [
            Stat::make('Total PR', DB::table('purchase_requests')->count())
                ->description('All purchase requests')
                ->descriptionIcon('heroicon-o-document-text')
                ->color('primary'),

            Stat::make('Pending', DB::table('purchase_requests')
                ->whereIn('status', ['draft', 'waiting_approval', 'in_review'])
                ->count())
                ->description('Needs attention')
                ->descriptionIcon('heroicon-o-clock')
                ->color('warning'),

            Stat::make('Approved', DB::table('purchase_requests')
                ->where('status', 'approved')
                ->count())
                ->description('Approved')
                ->descriptionIcon('heroicon-o-check-circle')
                ->color('success'),

            Stat::make('Completed', DB::table('purchase_requests')
                ->where('status', 'completed')
                ->count())
                ->description('Completed')
                ->descriptionIcon('heroicon-o-check-badge')
                ->color('success'),

            Stat::make('Rejected', DB::table('purchase_requests')
                ->where('status', 'rejected')
                ->count())
                ->description('Rejected')
                ->descriptionIcon('heroicon-o-x-circle')
                ->color('danger'),

            Stat::make('Need Revision', DB::table('purchase_requests')
                ->where('status', 'need_revision')
                ->count())
                ->description('Returned for revision')
                ->descriptionIcon('heroicon-o-pencil')
                ->color('warning'),
        ];
    }

    protected function getColumns(): int
    {
        return 3;
    }
}
