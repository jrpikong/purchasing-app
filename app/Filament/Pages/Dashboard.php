<?php

namespace App\Filament\Pages;

use App\Livewire\ListPurchaseRequest;
use Filament\Pages\Dashboard as BaseDashboard;

class Dashboard extends BaseDashboard
{
    public function getWidgets(): array
    {
        return [
            ListPurchaseRequest::class
        ];
    }

}
