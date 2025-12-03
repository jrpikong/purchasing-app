<?php

namespace App\Filament\Resources\PurchaseRequests\Tables;

use App\Models\PurchaseRequest;
use App\Models\User;
use App\Services\PurchaseRequestApprovalService;
use Filament\Actions\Action;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Actions\ForceDeleteBulkAction;
use Filament\Actions\RestoreBulkAction;
use Filament\Actions\ViewAction;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\DateTimePicker;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Tables;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Filters\Filter;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Filters\TrashedFilter;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Notification;
use Filament\Notifications\Notification as FilamentNotification;


class PurchaseRequestsTable
{
    public static function configure(Tables\Table $table): Tables\Table
    {
        return $table
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
                SelectFilter::make('status')
                    ->label('Status')
                    ->options([
                        'draft' => 'Draft',
                        'waiting_approval' => 'Waiting Approval',
                        'in_review' => 'In Review',
                        'approved' => 'Approved',
                        'rejected' => 'Rejected',
                        'need_revision' => 'Need Revision',
                        'completed' => 'Completed',
                        'cancelled' => 'Cancelled',
                    ]),

                SelectFilter::make('priority')
                    ->label('Priority')
                    ->options([
                        'low' => 'Low',
                        'medium' => 'Medium',
                        'high' => 'High',
                        'urgent' => 'Urgent',
                    ]),

                SelectFilter::make('department_id')
                    ->label('Department')
                    ->relationship('department', 'name'),

                Filter::make('requester')
                    ->label('Requester')
                    ->schema([
                        Select::make('requester_id')
                            ->relationship('requester', 'name')
                            ->searchable(),
                    ])
                    ->query(function (Builder $query, array $data) {
                        if (empty($data['requester_id'])) {
                            return $query;
                        }
                        return $query->where('requester_id', $data['requester_id']);
                    }),

                Filter::make('date_range')
                    ->schema([
                        DatePicker::make('started_at')->label('From'),
                        DatePicker::make('ended_at')->label('To'),
                    ])
                    ->query(function (Builder $query, array $data) {
                        if (!empty($data['started_at']) && !empty($data['ended_at'])) {
                            return $query->whereBetween('request_date', [$data['started_at'], $data['ended_at']]);
                        }
                        if (!empty($data['started_at'])) {
                            return $query->where('request_date', '>=', $data['started_at']);
                        }
                        if (!empty($data['ended_at'])) {
                            return $query->where('request_date', '<=', $data['ended_at']);
                        }
                        return $query;
                    }),

                Filter::make('my_pending')
                    ->label('My Pending Approvals')
                    ->query(fn (Builder $q) => $q->where('current_approver_id', Auth::id())->whereIn('status', ['waiting_approval', 'in_review'])),

                TrashedFilter::make(),
            ])
            ->recordActions([
                ViewAction::make(),

                EditAction::make()
                    ->visible(fn (PurchaseRequest $record) =>
                    auth()->user()->can('update', $record)
                    ),

                // Assign PIC Action
                Action::make('assign_pic')
                    ->label('Assign PIC')
                    ->icon('heroicon-o-user-plus')
                    ->color('info')
                    ->visible(fn (PurchaseRequest $record) =>
                    auth()->user()->can('assign', $record)
                    )
                    ->schema([
                        Select::make('assigned_pic_id')
                            ->label('Select PIC')
                            ->options(User::pluck('name', 'id'))
                            ->required()
                            ->searchable(),
                    ])
                    ->action(function (PurchaseRequest $record, array $data) {
                        $service = app(PurchaseRequestApprovalService::class);
                        $pic = User::find($data['assigned_pic_id']);

                        try {
                            $service->assignPic($record, $pic, auth()->user());

                            FilamentNotification::make()
                                ->success()
                                ->title('PIC Assigned')
                                ->body("Successfully assigned to {$pic->name}")
                                ->send();
                        } catch (\Exception $e) {
                            FilamentNotification::make()
                                ->danger()
                                ->title('Error')
                                ->body($e->getMessage())
                                ->send();
                        }
                    }),

                // Send for Approval Action
                Action::make('send_for_approval')
                    ->label('Send for Approval')
                    ->icon('heroicon-o-paper-airplane')
                    ->color('warning')
                    ->visible(fn (PurchaseRequest $record) =>
                    auth()->user()->can('sendForApproval', $record)
                    )
                    ->schema([
                        Select::make('approver_id')
                            ->label('Select Approver')
                            ->options(User::pluck('name', 'id'))
                            ->required()
                            ->searchable(),

                        DateTimePicker::make('deadline')
                            ->label('Approval Deadline')
                            ->nullable()
                            ->native(false),
                    ])
                    ->action(function (PurchaseRequest $record, array $data) {
                        $service = app(PurchaseRequestApprovalService::class);
                        $approver = User::find($data['approver_id']);

                        try {
                            $service->sendForApproval(
                                $record,
                                $approver,
                                auth()->user(),
                                isset($data['deadline']) ? \Carbon\Carbon::parse($data['deadline']) : null
                            );

                            FilamentNotification::make()
                                ->success()
                                ->title('Sent for Approval')
                                ->body("Successfully sent to {$approver->name}")
                                ->send();
                        } catch (\Exception $e) {
                            FilamentNotification::make()
                                ->danger()
                                ->title('Error')
                                ->body($e->getMessage())
                                ->send();
                        }
                    }),

                // Quick Approve Action
                Action::make('approve')
                    ->label('Approve')
                    ->icon('heroicon-o-check-circle')
                    ->color('success')
                    ->visible(fn (PurchaseRequest $record) =>
                    auth()->user()->can('approve', $record)
                    )
                    ->requiresConfirmation()
                    ->modalHeading('Approve Purchase Request')
                    ->modalDescription(fn (PurchaseRequest $record) =>
                    "Are you sure you want to approve PR {$record->pr_number}?"
                    )
                    ->schema([
                        Textarea::make('comment')
                            ->label('Comment (Optional)')
                            ->rows(2),
                    ])
                    ->action(function (PurchaseRequest $record, array $data) {
                        $service = app(PurchaseRequestApprovalService::class);

                        try {
                            $service->approve($record, auth()->user(), $data['comment'] ?? null);

                            FilamentNotification::make()
                                ->success()
                                ->title('Approved')
                                ->body("PR {$record->pr_number} has been approved")
                                ->send();
                        } catch (\Exception $e) {
                            FilamentNotification::make()
                                ->danger()
                                ->title('Error')
                                ->body($e->getMessage())
                                ->send();
                        }
                    }),

                // Quick Reject Action
                Action::make('reject')
                    ->label('Reject')
                    ->icon('heroicon-o-x-circle')
                    ->color('danger')
                    ->visible(fn (PurchaseRequest $record) =>
                    auth()->user()->can('reject', $record)
                    )
                    ->requiresConfirmation()
                    ->modalHeading('Reject Purchase Request')
                    ->schema([
                        Textarea::make('reason')
                            ->label('Rejection Reason')
                            ->required()
                            ->rows(3),

                        Textarea::make('comment')
                            ->label('Additional Comment')
                            ->rows(2),
                    ])
                    ->action(function (PurchaseRequest $record, array $data) {
                        $service = app(PurchaseRequestApprovalService::class);

                        try {
                            $service->reject($record, auth()->user(), $data['reason'], $data['comment'] ?? null);

                            FilamentNotification::make()
                                ->success()
                                ->title('Rejected')
                                ->body("PR {$record->pr_number} has been rejected")
                                ->send();
                        } catch (\Exception $e) {
                            FilamentNotification::make()
                                ->danger()
                                ->title('Error')
                                ->body($e->getMessage())
                                ->send();
                        }
                    }),

                // Cancel Action
                Action::make('cancel')
                    ->label('Cancel')
                    ->icon('heroicon-o-stop-circle')
                    ->color('danger')
                    ->visible(fn (PurchaseRequest $record) =>
                    auth()->user()->can('cancel', $record)
                    )
                    ->requiresConfirmation()
                    ->modalHeading('Cancel Purchase Request')
                    ->schema([
                        Textarea::make('cancel_reason')
                            ->label('Cancellation Reason')
                            ->required()
                            ->rows(3),
                    ])
                    ->action(function (PurchaseRequest $record, array $data) {
                        $previousStatus = $record->status;

                        $record->update([
                            'status' => PurchaseRequest::STATUS_CANCELLED,
                            'notes' => ($record->notes ? $record->notes . "\n\n" : '') .
                                "Cancelled: " . $data['cancel_reason'],
                        ]);

                        // Log history
                        $record->approvalHistories()->create([
                            'actor_id' => auth()->id(),
                            'action' => 'cancelled',
                            'comment' => $data['cancel_reason'],
                            'from_status' => $previousStatus,
                            'to_status' => PurchaseRequest::STATUS_CANCELLED,
                            'acted_at' => now(),
                        ]);

                        FilamentNotification::make()
                            ->success()
                            ->title('PR Cancelled')
                            ->body("PR {$record->pr_number} has been cancelled")
                            ->send();
                    }),

            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make(),
                    ForceDeleteBulkAction::make(),
                    RestoreBulkAction::make(),
                ]),
            ]);
    }
}
