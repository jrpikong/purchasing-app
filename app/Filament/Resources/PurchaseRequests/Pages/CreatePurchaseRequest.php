<?php

namespace App\Filament\Resources\PurchaseRequests\Pages;

use App\Filament\Resources\PurchaseRequests\PurchaseRequestResource;
use App\Models\PurchaseRequest;
use Filament\Resources\Pages\CreateRecord;
use Illuminate\Database\Eloquent\Model;

class CreatePurchaseRequest extends CreateRecord
{
    protected static string $resource = PurchaseRequestResource::class;

    protected static ?string $title = 'Buat Purchase Request Baru';

    public function getBreadcrumbs(): array
    {
        return [
            PurchaseRequestResource::getUrl() => 'Purchase Request',
            '#' => 'Buat Baru',
        ];
    }

    protected function getRedirectUrl(): string
    {
        return $this->getResource()::getUrl('view', ['record' => $this->getRecord()]);
    }

    protected function mutateFormDataBeforeCreate(array $data): array
    {
        $data['requester_id'] = auth()->id();
        $data['status']       = 'draft';
        $data['request_date'] = $data['request_date'] ?? now()->toDateString();

        // Set department from user's department if not provided
        if (empty($data['department_id'])) {
            $data['department_id'] = auth()->user()->department_id;
        }

        return $data;
    }

    protected function getCreatedNotificationTitle(): ?string
    {
        return 'Purchase Request berhasil dibuat sebagai Draft';
    }
}
