<?php

namespace App\Filament\Resources\PurchaseRequests\Pages;

use App\Filament\Resources\PurchaseRequests\PurchaseRequestResource;
use Filament\Actions\DeleteAction;
use Filament\Actions\ForceDeleteAction;
use Filament\Actions\RestoreAction;
use Filament\Actions\ViewAction;
use Filament\Resources\Pages\EditRecord;

class EditPurchaseRequest extends EditRecord
{
    protected static string $resource = PurchaseRequestResource::class;

    protected function getHeaderActions(): array
    {
        return [
            ViewAction::make(),
            DeleteAction::make()
                ->visible(fn ($record) => auth()->user()->can('delete', $record)),
            ForceDeleteAction::make()
                ->visible(fn ($record) => auth()->user()->can('forceDelete', $record)),
            RestoreAction::make()
                ->visible(fn ($record) => auth()->user()->can('restore', $record)),
        ];
    }

    /**
     * Authorize access - ensure user can edit this PR
     */
    protected function authorizeAccess(): void
    {
        parent::authorizeAccess();

        abort_unless(
            auth()->user()->can('update', $this->getRecord()),
            403,
            'You do not have permission to edit this purchase request.'
        );
    }
}
