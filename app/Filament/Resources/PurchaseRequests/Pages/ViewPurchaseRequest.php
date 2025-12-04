<?php

namespace App\Filament\Resources\PurchaseRequests\Pages;

use App\Filament\Resources\PurchaseRequests\PurchaseRequestResource;
use Filament\Actions\EditAction;
use Filament\Resources\Pages\ViewRecord;

class ViewPurchaseRequest extends ViewRecord
{
    protected static string $resource = PurchaseRequestResource::class;

    protected function getHeaderActions(): array
    {
        return [
            EditAction::make()
                ->visible(fn ($record) => auth()->user()->can('update', $record)),
        ];
    }

    /**
     * Authorize access - ensure user can view this PR
     */
    protected function authorizeAccess(): void
    {
        parent::authorizeAccess();

        abort_unless(
            auth()->user()->can('view', $this->getRecord()),
            403,
            'You do not have permission to view this purchase request.'
        );
    }
}
