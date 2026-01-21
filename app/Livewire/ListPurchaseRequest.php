<?php

namespace App\Livewire;

use App\Models\PurchaseRequest;
use Filament\Actions\BulkActionGroup;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Filament\Widgets\TableWidget;
use Illuminate\Database\Eloquent\Builder;

class ListPurchaseRequest extends TableWidget
{
     protected int | string | array $columnSpan = 'full'; // Atur lebar kolom

    public function table(Table $table): Table
    {
        return $table
            ->query(fn (): Builder =>
            PurchaseRequest::query()
                ->latest()
                ->limit(10)
            )
            ->columns([
                TextColumn::make('pr_number')
                    ->label('PR #')
                    ->searchable()
                    ->sortable()
                    ->url(fn (PurchaseRequest $record): string => route('filament.admin.resources.purchase-requests.view', $record)),

                TextColumn::make('requester.name')
                    ->label('Requester')
                    ->searchable()
                    ->sortable()
                    ->toggleable(),

                TextColumn::make('department.name')
                    ->label('Department')
                    ->searchable()
                    ->sortable()
                    ->toggleable(),

                TextColumn::make('purpose')
                    ->label('Purpose')
                    ->limit(50)
                    ->wrap()
                    ->toggleable(),

                TextColumn::make('required_date')
                    ->label('Required Date')
                    ->date()
                    ->sortable()
                    ->toggleable(),

                TextColumn::make('request_date')
                    ->label('Request Date')
                    ->date()
                    ->sortable()
                    ->toggleable(),

                TextColumn::make('total_amount')
                    ->label('Total Amount')
                    ->sortable()
                    ->formatStateUsing(fn ($state) => $state !== null ? 'Rp ' . number_format((float)$state, 2, ',', '.') : '-')
                    ->toggleable(),

                TextColumn::make('priority')
                    ->label('Priority')
                    ->sortable()
                    ->toggleable(),

                TextColumn::make('status')
                    ->label('Status')
                    ->sortable()
                    ->toggleable(),

                TextColumn::make('currentApprover.name')
                    ->label('Current Approver')
                    ->sortable()
                    ->toggleable(),

                TextColumn::make('created_at')
                    ->label('Created')
                    ->since()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),

                TextColumn::make('updated_at')
                    ->label('Updated')
                    ->since()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),

                IconColumn::make('has_attachments')
                    ->label('Files')
                    ->boolean()
                    ->getStateUsing(fn (PurchaseRequest $record) => $record->attachments()->exists())
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                //
            ])
            ->headerActions([
                //
            ])
            ->recordActions([
                //
            ]);
    }
}
