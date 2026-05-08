<?php

namespace App\Filament\Pages;

use Filament\Pages\Dashboard as BaseDashboard;
use Illuminate\Support\Facades\Auth;

class Dashboard extends BaseDashboard
{
    public function mount(): void
    {
        $user = Auth::user();

        // Redirect to appropriate dashboard based on role priority
        if ($user->hasRole(['admin', 'super_admin'])) {
            $this->redirect(AdminDashboard::getUrl(), navigate: true);
            return;
        }

        if ($user->hasRole(['section_head', 'division_head', 'finance_admin', 'treasurer'])) {
            $this->redirect(ApproverDashboard::getUrl(), navigate: true);
            return;
        }

        if ($user->hasRole(['requester'])) {
            $this->redirect(RequesterDashboard::getUrl(), navigate: true);
            return;
        }

        // Fallback - show default dashboard if no specific role
        parent::mount();
    }

    public function getWidgets(): array
    {
        return [];
    }
}
