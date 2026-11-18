<?php

namespace App\Filament\Resources\EmailLogs\Tables;

use App\Models\EmailLog;
use Filament\Actions\ViewAction;
use Filament\Forms\Components\DatePicker;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\Filter;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Str;

class EmailLogsTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->modifyQueryUsing(fn (Builder $query) => $query->with(['recipient', 'purchaseRequest']))
            ->defaultSort('created_at', 'desc')
            ->poll('30s')
            ->columns([
                TextColumn::make('created_at')
                    ->label('Waktu')
                    ->dateTime('d M Y H:i')
                    ->timezone('Asia/Jakarta')
                    ->sortable(),

                TextColumn::make('status')
                    ->label('Status')
                    ->badge()
                    ->formatStateUsing(fn (string $state) => EmailLog::statusLabels()[$state] ?? $state)
                    ->color(fn (string $state) => match ($state) {
                        EmailLog::STATUS_SENT => 'success',
                        EmailLog::STATUS_FAILED => 'danger',
                        default => 'warning',
                    })
                    ->icon(fn (string $state) => match ($state) {
                        EmailLog::STATUS_SENT => 'heroicon-o-check-circle',
                        EmailLog::STATUS_FAILED => 'heroicon-o-x-circle',
                        default => 'heroicon-o-clock',
                    }),

                TextColumn::make('email_type')
                    ->label('Jenis')
                    ->formatStateUsing(fn (EmailLog $record) => $record->email_type_label)
                    ->badge()
                    ->color('gray')
                    ->toggleable(),

                TextColumn::make('recipient.name')
                    ->label('Penerima')
                    ->description(fn (EmailLog $record) => $record->email_to)
                    ->searchable(query: fn (Builder $query, string $search) => $query->where(fn ($q) => $q
                        ->where('email_to', 'like', "%{$search}%")
                        ->orWhereHas('recipient', fn ($r) => $r->where('name', 'like', "%{$search}%"))))
                    ->placeholder('—'),

                TextColumn::make('email_subject')
                    ->label('Subjek')
                    ->limit(60)
                    ->tooltip(fn (EmailLog $record) => Str::length($record->email_subject) > 60 ? $record->email_subject : null)
                    ->searchable()
                    ->wrap(),

                TextColumn::make('purchaseRequest.pr_number')
                    ->label('PR #')
                    ->searchable()
                    ->url(fn (EmailLog $record) => $record->purchaseRequest
                        ? route('filament.admin.resources.purchase-requests.view', $record->purchaseRequest)
                        : null)
                    ->placeholder('—'),

                TextColumn::make('retry_count')
                    ->label('Retry')
                    ->numeric()
                    ->alignCenter()
                    ->color(fn (int $state) => $state > 0 ? 'warning' : 'gray')
                    ->toggleable(),

                TextColumn::make('sent_at')
                    ->label('Terkirim pada')
                    ->dateTime('d M Y H:i:s')
                    ->timezone('Asia/Jakarta')
                    ->placeholder('—')
                    ->toggleable(isToggledHiddenByDefault: true),

                TextColumn::make('error_message')
                    ->label('Error')
                    ->limit(80)
                    ->tooltip(fn (EmailLog $record) => $record->error_message)
                    ->color('danger')
                    ->placeholder('—')
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                SelectFilter::make('status')
                    ->label('Status')
                    ->options(EmailLog::statusLabels())
                    ->query(fn (Builder $query, array $data) => filled($data['value'] ?? null)
                        ? $query->status($data['value'])
                        : $query),

                SelectFilter::make('email_type')
                    ->label('Jenis email')
                    ->options(EmailLog::typeLabels())
                    ->multiple(),

                SelectFilter::make('recipient_id')
                    ->label('Penerima')
                    ->relationship('recipient', 'name')
                    ->searchable()
                    ->preload(),

                Filter::make('created_at')
                    ->label('Tanggal')
                    ->schema([
                        DatePicker::make('from')->label('Dari'),
                        DatePicker::make('until')->label('Sampai'),
                    ])
                    ->query(fn (Builder $query, array $data) => $query
                        ->when($data['from'] ?? null, fn ($q, $date) => $q->whereDate('created_at', '>=', $date))
                        ->when($data['until'] ?? null, fn ($q, $date) => $q->whereDate('created_at', '<=', $date)))
                    ->indicateUsing(function (array $data): array {
                        $indicators = [];
                        if ($data['from'] ?? null) {
                            $indicators[] = 'Dari ' . \Carbon\Carbon::parse($data['from'])->format('d M Y');
                        }
                        if ($data['until'] ?? null) {
                            $indicators[] = 'Sampai ' . \Carbon\Carbon::parse($data['until'])->format('d M Y');
                        }
                        return $indicators;
                    }),
            ])
            ->recordActions([
                ViewAction::make(),
            ])
            ->emptyStateHeading('Belum ada email tercatat')
            ->emptyStateDescription('Setiap email purchase request yang dikirim sistem akan muncul di sini.')
            ->emptyStateIcon('heroicon-o-envelope');
    }
}
