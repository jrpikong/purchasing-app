<?php

namespace App\Filament\Resources\PurchaseRequests\Tables;

use App\Enums\Priority;
use App\Enums\PurchaseRequestStatus;
use App\Filament\Exports\PurchaseRequestExporter;
use App\Models\PurchaseRequest;
use App\Models\User;
use App\Services\PurchaseRequestApprovalService;
use Filament\Actions\Action;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Actions\ExportAction;
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
            ->modifyQueryUsing(
                fn (Builder $query) => $query
                    ->with(['requester', 'department', 'currentApprover'])
                    ->withCount('attachments')
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
                    ->getStateUsing(fn (PurchaseRequest $record) => $record->attachments_count > 0)
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                SelectFilter::make('status')
                    ->label('Status')
                    ->options(PurchaseRequestStatus::options()),

                SelectFilter::make('priority')
                    ->label('Priority')
                    ->options(Priority::options()),

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
                            ->options(
                                // Same definition of "admin" as emails & policies (column, Spatie role or is_admin)
                                fn () => User::query()->admins()->orderBy('name')->pluck('name', 'id')
                            )
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
                        DateTimePicker::make('deadline')
                            ->label('Approval Deadline')
                            ->nullable()
                            ->native(false),
                    ])
                    ->action(function (PurchaseRequest $record, array $data) {
                        $service = app(PurchaseRequestApprovalService::class);

                        try {
                            // The service resolves the first approver (skipping the requester)
                            // and explains exactly what's wrong if the flow can't be routed.
                            $sent = $service->sendForApproval(
                                $record,
                                null,
                                auth()->user(),
                                isset($data['deadline']) ? \Carbon\Carbon::parse($data['deadline']) : null
                            );

                            FilamentNotification::make()
                                ->success()
                                ->title('Sent for Approval')
                                ->body('Successfully sent to ' . ($sent->currentApprover?->name ?? '—'))
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
                        $service = app(PurchaseRequestApprovalService::class);

                        try {
                            $service->cancel($record, auth()->user(), $data['cancel_reason']);

                            FilamentNotification::make()
                                ->success()
                                ->title('PR Cancelled')
                                ->body("PR {$record->pr_number} has been cancelled")
                                ->send();
                        } catch (\Exception $e) {
                            FilamentNotification::make()
                                ->danger()
                                ->title('Error')
                                ->body($e->getMessage())
                                ->send();
                        }
                    }),

                // Request Revision Action
                Action::make('request_revision')
                    ->label('Request Revision')
                    ->icon('heroicon-o-pencil')
                    ->color('warning')
                    ->visible(fn (PurchaseRequest $record) =>
                    auth()->user()->can('requestRevision', $record)
                    )
                    ->requiresConfirmation()
                    ->modalHeading('Request Revision')
                    ->modalDescription(fn (PurchaseRequest $record) =>
                    "Request changes to PR {$record->pr_number}?"
                    )
                    ->schema([
                        Textarea::make('revision_notes')
                            ->label('Revision Notes')
                            ->required()
                            ->rows(3)
                            ->helperText('Describe what needs to be revised'),
                    ])
                    ->action(function (PurchaseRequest $record, array $data) {
                        $service = app(PurchaseRequestApprovalService::class);

                        try {
                            $service->requestRevision($record, auth()->user(), $data['revision_notes']);

                            FilamentNotification::make()
                                ->success()
                                ->title('Revision Requested')
                                ->body("PR {$record->pr_number} has been returned for revision")
                                ->send();
                        } catch (\Exception $e) {
                            FilamentNotification::make()
                                ->danger()
                                ->title('Error')
                                ->body($e->getMessage())
                                ->send();
                        }
                    }),

                // Resubmit after revision (requester)
                static::resubmitAction(),

                // Resend approval link (admin) — expired/lost email
                static::resendApprovalLinkAction(),

                // Mark as Completed Action
                Action::make('mark_completed')
                    ->label('Mark as Completed')
                    ->icon('heroicon-o-check-badge')
                    ->color('success')
                    ->visible(fn (PurchaseRequest $record) =>
                    auth()->user()->can('markCompleted', $record)
                    )
                    ->requiresConfirmation()
                    ->modalHeading('Mark PR as Completed')
                    ->modalDescription(fn (PurchaseRequest $record) =>
                    "Confirm that PR {$record->pr_number} has been fully processed?"
                    )
                    ->action(function (PurchaseRequest $record) {
                        $service = app(PurchaseRequestApprovalService::class);

                        try {
                            $service->markCompleted($record, auth()->user());

                            FilamentNotification::make()
                                ->success()
                                ->title('PR Completed')
                                ->body("PR {$record->pr_number} has been marked as completed")
                                ->send();
                        } catch (\Exception $e) {
                            FilamentNotification::make()
                                ->danger()
                                ->title('Error')
                                ->body($e->getMessage())
                                ->send();
                        }
                    }),
            ])
            ->headerActions([
                ExportAction::make()
                    ->exporter(PurchaseRequestExporter::class),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make(),
                    ForceDeleteBulkAction::make(),
                    RestoreBulkAction::make(),
                ]),
            ]);
    }

    /**
     * Admin re-issues the approval email with a fresh link. Shared with the view page.
     */
    public static function resendApprovalLinkAction(): Action
    {
        return Action::make('resend_approval_link')
            ->label('Kirim Ulang Link Approval')
            ->icon('heroicon-o-envelope')
            ->color('gray')
            ->visible(fn (PurchaseRequest $record) =>
                auth()->user()->can('resendApprovalLink', $record)
            )
            ->requiresConfirmation()
            ->modalHeading('Kirim Ulang Link Approval')
            ->modalDescription(fn (PurchaseRequest $record) =>
                'Email approval baru akan dikirim ke ' . ($record->currentApprover?->name ?? 'approver saat ini')
                . '. Link lama tidak berlaku lagi.'
            )
            ->action(function (PurchaseRequest $record) {
                try {
                    $pr = app(PurchaseRequestApprovalService::class)
                        ->resendApprovalLink($record, auth()->user());

                    FilamentNotification::make()
                        ->success()
                        ->title('Link approval dikirim ulang')
                        ->body('Email dikirim ke ' . ($pr->currentApprover?->name ?? '—'))
                        ->send();
                } catch (\Exception $e) {
                    FilamentNotification::make()
                        ->danger()
                        ->title('Error')
                        ->body($e->getMessage())
                        ->send();
                }
            });
    }

    /**
     * Requester resubmits a PR after revising it. Shared with the view page.
     */
    public static function resubmitAction(): Action
    {
        return Action::make('resubmit')
            ->label('Submit Ulang')
            ->icon('heroicon-o-arrow-path')
            ->color('primary')
            ->visible(fn (PurchaseRequest $record) =>
                auth()->user()->can('resubmit', $record)
            )
            ->requiresConfirmation()
            ->modalHeading('Submit Ulang Purchase Request')
            ->modalDescription(fn (PurchaseRequest $record) =>
                "Pastikan PR {$record->pr_number} sudah direvisi. PIC/admin akan diberi tahu untuk mengirim ulang ke approval."
            )
            ->schema([
                Textarea::make('comment')
                    ->label('Catatan revisi (opsional)')
                    ->helperText('Jelaskan apa saja yang sudah diperbaiki')
                    ->rows(3),
            ])
            ->action(function (PurchaseRequest $record, array $data) {
                try {
                    app(PurchaseRequestApprovalService::class)
                        ->resubmit($record, auth()->user(), $data['comment'] ?? null);

                    FilamentNotification::make()
                        ->success()
                        ->title('PR disubmit ulang')
                        ->body("PR {$record->pr_number} sudah dikirim kembali ke PIC/admin untuk direview")
                        ->send();
                } catch (\Exception $e) {
                    FilamentNotification::make()
                        ->danger()
                        ->title('Error')
                        ->body($e->getMessage())
                        ->send();
                }
            });
    }
}
