<?php

namespace App\Filament\Resources\EmailLogs\Pages;

use App\Filament\Resources\EmailLogs\EmailLogResource;
use App\Models\EmailLog;
use Filament\Resources\Pages\ListRecords;
use Filament\Schemas\Components\Tabs\Tab;
use Illuminate\Database\Eloquent\Builder;

class ListEmailLogs extends ListRecords
{
    protected static string $resource = EmailLogResource::class;

    protected ?string $subheading = 'Riwayat semua email purchase request yang dikirim sistem.';

    public function getTabs(): array
    {
        $count = fn (string $status) => EmailLog::query()->status($status)->count();

        return [
            'all' => Tab::make('Semua'),

            EmailLog::STATUS_FAILED => Tab::make('Gagal')
                ->icon('heroicon-o-x-circle')
                ->badge(fn () => $count(EmailLog::STATUS_FAILED) ?: null)
                ->badgeColor('danger')
                ->modifyQueryUsing(fn (Builder $query) => $query->status(EmailLog::STATUS_FAILED)),

            EmailLog::STATUS_PENDING => Tab::make('Menunggu')
                ->icon('heroicon-o-clock')
                ->badge(fn () => $count(EmailLog::STATUS_PENDING) ?: null)
                ->badgeColor('warning')
                ->modifyQueryUsing(fn (Builder $query) => $query->status(EmailLog::STATUS_PENDING)),

            EmailLog::STATUS_SENT => Tab::make('Terkirim')
                ->icon('heroicon-o-check-circle')
                ->modifyQueryUsing(fn (Builder $query) => $query->status(EmailLog::STATUS_SENT)),
        ];
    }
}
