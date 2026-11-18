<?php

namespace App\Filament\Resources\EmailLogs\Schemas;

use App\Models\EmailLog;
use Filament\Infolists\Components\TextEntry;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;
use Filament\Support\Enums\FontFamily;

class EmailLogInfolist
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make('Email')
                    ->columns(2)
                    ->columnSpanFull()
                    ->schema([
                        TextEntry::make('status')
                            ->label('Status')
                            ->badge()
                            ->formatStateUsing(fn (string $state) => EmailLog::statusLabels()[$state] ?? $state)
                            ->color(fn (string $state) => match ($state) {
                                EmailLog::STATUS_SENT => 'success',
                                EmailLog::STATUS_FAILED => 'danger',
                                default => 'warning',
                            }),

                        TextEntry::make('email_type_label')
                            ->label('Jenis')
                            ->badge()
                            ->color('gray'),

                        TextEntry::make('email_subject')
                            ->label('Subjek')
                            ->columnSpanFull(),

                        TextEntry::make('recipient.name')
                            ->label('Penerima')
                            ->placeholder('—'),

                        TextEntry::make('email_to')
                            ->label('Alamat email')
                            ->copyable(),

                        TextEntry::make('email_cc')
                            ->label('CC')
                            ->placeholder('—'),

                        TextEntry::make('purchaseRequest.pr_number')
                            ->label('Purchase Request')
                            ->url(fn (EmailLog $record) => $record->purchaseRequest
                                ? route('filament.admin.resources.purchase-requests.view', $record->purchaseRequest)
                                : null)
                            ->placeholder('—'),
                    ]),

                Section::make('Pengiriman')
                    ->columns(3)
                    ->columnSpanFull()
                    ->schema([
                        TextEntry::make('created_at')
                            ->label('Dibuat (mulai dikirim)')
                            ->dateTime('d M Y H:i:s')
                            ->timezone('Asia/Jakarta'),

                        TextEntry::make('sent_at')
                            ->label('Terkirim pada')
                            ->dateTime('d M Y H:i:s')
                            ->timezone('Asia/Jakarta')
                            ->placeholder('Belum terkirim'),

                        TextEntry::make('retry_count')
                            ->label('Jumlah retry'),

                        TextEntry::make('notification_id')
                            ->label('Notification ID')
                            ->copyable()
                            ->placeholder('—')
                            ->columnSpanFull(),
                    ]),

                Section::make('Error')
                    ->columnSpanFull()
                    ->visible(fn (EmailLog $record) => filled($record->error_message))
                    ->schema([
                        TextEntry::make('error_message')
                            ->hiddenLabel()
                            ->color('danger')
                            ->fontFamily(FontFamily::Mono)
                            ->copyable(),
                    ]),
            ]);
    }
}
