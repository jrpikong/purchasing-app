<?php

namespace App\Filament\Resources\PurchaseRequests\Schemas;

use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\DateTimePicker;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Textarea;
use Filament\Schemas\Schema;

class PurchaseRequestForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                TextInput::make('pr_number')
                    ->required(),
                TextInput::make('requester_id')
                    ->required()
                    ->numeric(),
                TextInput::make('department_id')
                    ->required()
                    ->numeric(),
                DatePicker::make('request_date')
                    ->required(),
                DatePicker::make('required_date')
                    ->required(),
                Textarea::make('purpose')
                    ->required()
                    ->columnSpanFull(),
                TextInput::make('total_amount')
                    ->required()
                    ->numeric()
                    ->default(0.0),
                Select::make('status')
                    ->options([
            'draft' => 'Draft',
            'waiting_approval' => 'Waiting approval',
            'approved' => 'Approved',
            'rejected' => 'Rejected',
            'need_revision' => 'Need revision',
            'completed' => 'Completed',
            'cancelled' => 'Cancelled',
        ])
                    ->default('draft')
                    ->required(),
                TextInput::make('current_approver_id')
                    ->numeric(),
                Select::make('priority')
                    ->options(['low' => 'Low', 'medium' => 'Medium', 'high' => 'High', 'urgent' => 'Urgent'])
                    ->default('medium')
                    ->required(),
                Textarea::make('notes')
                    ->columnSpanFull(),
                Textarea::make('rejection_reason')
                    ->columnSpanFull(),
                DateTimePicker::make('approved_at'),
                DateTimePicker::make('rejected_at'),
            ]);
    }
}
