<?php

namespace App\Filament\Resources\PurchaseRequests\Pages;

use App\Filament\Resources\PurchaseRequests\PurchaseRequestResource;
use App\Models\PurchaseRequest;
use Filament\Resources\Pages\CreateRecord;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;

class CreatePurchaseRequest extends CreateRecord
{
    protected static string $resource = PurchaseRequestResource::class;

//    protected function handleRecordCreation(array $data): Model
//    {
//        // set requester as current user if not provided
//        if (empty($data['requester_id']) && auth()->check()) {
//            $data['requester_id'] = auth()->id();
//        }
//        // generate PR number if not provided
//        if (empty($data['pr_number'])) {
//            $data['pr_number'] = $this->generatePRNumber();
//        }
//        $data['submitted_at'] = now()->toDateTimeString();
//        // set initial status to draft (or waiting_approval if you want auto-send)
//
//        $data['status'] = $data['status'] ?? 'waiting_approval';
//        return parent::handleRecordCreation($data);
//    }

    /**
     * Generate PR Number
     */
    public function generatePRNumber(): string
    {
        $year = date('Y');
        $month = date('m');

        $lastPR = PurchaseRequest::query()->whereYear('created_at', $year)
            ->whereMonth('created_at', $month)
            ->latest('id')
            ->first();

        $sequence = $lastPR ?
            intval(substr($lastPR->pr_number, -4)) + 1 : 1;

        return sprintf("PR/%s/%s/%04d", $year, $month, $sequence);
    }
}
