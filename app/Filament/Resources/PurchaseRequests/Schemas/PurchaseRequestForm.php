<?php

namespace App\Filament\Resources\PurchaseRequests\Schemas;

use App\Filament\Resources\PurchaseRequests\Pages\EditPurchaseRequest;
use App\Models\PurchaseRequest;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\DateTimePicker;
use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Textarea;
use Filament\Infolists\Components\TextEntry;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;

class PurchaseRequestForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make('Request Information')
                    ->schema([
                        Select::make('department_id')
                            ->label('Department')
                            ->relationship('department', 'name')
                            ->required()
                            ->searchable()
                            ->preload(),

                        DatePicker::make('request_date')
                            ->label('Date of Request')
                            ->required()
                            ->default(now())
                            ->native(false),

                        DatePicker::make('required_date')
                            ->label('Required By Date')
                            ->required()
                            ->native(false)
                            ->after('request_date'),

                        Select::make('priority')
                            ->options([
                                'low' => 'Low',
                                'medium' => 'Medium',
                                'high' => 'High',
                                'urgent' => 'Urgent',
                            ])
                            ->default('medium')
                            ->required()
                            ->visible(fn($livewire) => $livewire instanceof EditPurchaseRequest),
                    ])->columns(2)
                    ,

                Section::make('Purchase Details')
                    ->schema([
                        Textarea::make('purpose')
                            ->label('Purpose of Purchase')
                            ->required()
                            ->rows(3)
                            ->columnSpanFull(),

                        TextInput::make('total_amount')
                            ->label('Total Amount (Optional)')
                            ->numeric()
                            ->prefix('Rp')
                            ->nullable(),

                        Textarea::make('notes')
                            ->label('Additional Notes')
                            ->rows(2)
                            ->nullable()
                            ->columnSpanFull(),
                    ]),

                Section::make('Vendor Information')
                    ->schema([
                        TextInput::make('preferred_vendor_name')
                            ->label('Preferred Vendor Name')
                            ->maxLength(255)
                            ->nullable(),

                        Textarea::make('preferred_vendor_reason')
                            ->label('Reason for Choosing Vendor')
                            ->rows(2)
                            ->nullable()
                            ->columnSpanFull(),

                        TextInput::make('vendor_marketplace_link_1')
                            ->label('Vendor Quotation Link 1')
                            ->url()
                            ->nullable()
                            ->columnSpanFull(),

                        TextInput::make('vendor_marketplace_link_2')
                            ->label('Vendor Quotation Link 2')
                            ->url()
                            ->nullable()
                            ->columnSpanFull(),

                        FileUpload::make('quotation_files')
                            ->label('Upload Quotation Files')
                            ->multiple()
                            ->acceptedFileTypes(['application/pdf'])
                            ->maxSize(10240) // 10MB
                            ->directory('quotations')
                            ->visibility('private')
                            ->downloadable()
                            ->columnSpanFull(),
                    ]),

                // Admin-only fields (only visible in Edit)
                Section::make('Assignment & Approval')
                    ->schema([
                        Select::make('assigned_pic_id')
                            ->label('Assigned PIC')
                            ->relationship('assignedPic', 'name')
                            ->searchable()
                            ->preload()
                            ->nullable(),

                        Select::make('current_approver_id')
                            ->label('Current Approver')
                            ->relationship('currentApprover', 'name')
                            ->searchable()
                            ->preload()
                            ->nullable(),

                        Select::make('status')
                            ->options([
                                'draft' => 'Draft',
                                'waiting_approval' => 'Waiting Approval',
                                'in_review' => 'In Review',
                                'approved' => 'Approved',
                                'rejected' => 'Rejected',
                                'need_revision' => 'Need Revision',
                                'completed' => 'Completed',
                                'cancelled' => 'Cancelled',
                            ])
                            ->required()
                            ->disabled(),

                        DateTimePicker::make('approval_deadline')
                            ->label('Approval Deadline')
                            ->nullable()
                            ->native(false),
                    ])
                    ->columns(2)
                    ->visible(fn($livewire) => $livewire instanceof EditPurchaseRequest),

                Section::make('System Information')
                    ->schema([
                        TextEntry::make('pr_number')
                            ->label('PR Number')
                            ->tooltip(fn($record) => $record?->pr_number ?? 'Auto-generated'),

                        TextEntry::make('requester')
                            ->label('Requester')
                            ->tooltip(fn($record) => $record?->requester?->name),

                        TextEntry::make('submitted_at')
                            ->label('Submitted At')
                            ->tooltip(fn($record) => $record?->submitted_at?->format('d M Y H:i')),

                        TextEntry::make('approved_at')
                            ->label('Approved At')
                            ->tooltip(fn($record) => $record?->approved_at?->format('d M Y H:i')),
                    ])
                    ->columns(2)
                    ->visible(fn($livewire) => $livewire instanceof EditPurchaseRequest),

            ]);
    }
}
