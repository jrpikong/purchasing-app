<?php

namespace App\Filament\Resources\PurchaseRequests\Schemas;

use Filament\Infolists\Components\RepeatableEntry;
use Filament\Infolists\Components\TextEntry;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;

class PurchaseRequestInfolist
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make('Purchase Request Details')
                    ->schema([
                        TextEntry::make('pr_number')
                            ->label('PR Number')
                            ->badge()
                            ->color('primary')
                            ->size('lg'),

                        TextEntry::make('status')
                            ->badge(),

                        TextEntry::make('priority')
                            ->badge(),

                        TextEntry::make('requester.name')
                            ->label('Requested By'),

                        TextEntry::make('department.name')
                            ->label('Department'),

                        TextEntry::make('request_date')
                            ->label('Request Date')
                            ->date('d F Y'),

                        TextEntry::make('required_date')
                            ->label('Required Date')
                            ->date('d F Y'),

                        TextEntry::make('submitted_at')
                            ->label('Submitted At')
                            ->dateTime('d F Y H:i'),
                    ])->columns(3),

                Section::make('Purchase Information')
                    ->schema([
                        TextEntry::make('purpose')
                            ->label('Purpose')
                            ->columnSpanFull(),

                        TextEntry::make('total_amount')
                            ->label('Total Amount')
                            ->money('IDR')
                            ->placeholder('Not specified'),

                        TextEntry::make('notes')
                            ->label('Notes')
                            ->placeholder('No notes')
                            ->columnSpanFull(),
                    ])->columns(2),

                Section::make('Vendor Information')
                    ->schema([
                        TextEntry::make('preferred_vendor_name')
                            ->label('Preferred Vendor')
                            ->placeholder('Not specified'),

                        TextEntry::make('preferred_vendor_reason')
                            ->label('Reason')
                            ->placeholder('Not specified')
                            ->columnSpanFull(),

                        TextEntry::make('vendor_marketplace_link_1')
                            ->label('Vendor Link 1')
                            ->url(fn($state) => $state)
                            ->openUrlInNewTab()
                            ->placeholder('Not provided'),

                        TextEntry::make('vendor_marketplace_link_2')
                            ->label('Vendor Link 2')
                            ->url(fn($state) => $state)
                            ->openUrlInNewTab()
                            ->placeholder('Not provided'),
                    ])->columns(2),

                Section::make('Attachments')
                    ->schema([
                        RepeatableEntry::make('quotation_files')
                            ->label('Quotation Files')
                            ->schema([
                                TextEntry::make('filename')
                                    ->label('File')
                                    ->url(fn($state) => \Storage::url($state))
                                    ->openUrlInNewTab(),
                            ])
                            ->columns(1)
                            ->placeholder('No files uploaded'),
                    ])
                    ->collapsible()
                    ->visible(fn($record) => !empty($record->quotation_files)),

                Section::make('Assignment & Approval')
                    ->schema([
                        TextEntry::make('assignedPic.name')
                            ->label('Assigned PIC')
                            ->placeholder('Not assigned'),

                        TextEntry::make('currentApprover.name')
                            ->label('Current Approver')
                            ->placeholder('Not assigned'),

                        TextEntry::make('finalApprover.name')
                            ->label('Final Approver')
                            ->placeholder('Not approved yet')
                            ->visible(fn($record) => $record->approved_at),

                        TextEntry::make('approved_at')
                            ->label('Approved At')
                            ->dateTime('d F Y H:i')
                            ->visible(fn($record) => $record->approved_at),

                        TextEntry::make('rejected_at')
                            ->label('Rejected At')
                            ->dateTime('d F Y H:i')
                            ->visible(fn($record) => $record->rejected_at),

                        TextEntry::make('rejection_reason')
                            ->label('Rejection Reason')
                            ->columnSpanFull()
                            ->visible(fn($record) => $record->rejected_at),
                    ])->columns(2),

                Section::make('Approval History')
                    ->schema([
                        RepeatableEntry::make('approvalHistories')
                            ->label('')
                            ->schema([
                                TextEntry::make('acted_at')
                                    ->label('Date/Time')
                                    ->dateTime('d M Y H:i'),

                                TextEntry::make('actor.name')
                                    ->label('Actor'),

                                TextEntry::make('action')
                                    ->label('Action')
                                    ->badge(),

                                TextEntry::make('comment')
                                    ->label('Comment')
                                    ->placeholder('No comment')
                                    ->columnSpanFull(),
                            ])
                            ->columns(3),
                    ])
                    ->collapsible()
                    ->collapsed(false),

            ]);
    }
}
