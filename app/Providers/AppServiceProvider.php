<?php

namespace App\Providers;

use App\Filament\Responses\CustomLoginResponse;
use App\Services\PurchaseRequestApprovalService;
use App\Support\EmailLogRecorder;
use Illuminate\Notifications\Events\NotificationFailed;
use Illuminate\Notifications\Events\NotificationSending;
use Illuminate\Notifications\Events\NotificationSent;
use Illuminate\Support\Facades\Event;
use Filament\Auth\Http\Responses\LoginResponse;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        $this->app->bind(LoginResponse::class, CustomLoginResponse::class);

        $this->app->singleton(PurchaseRequestApprovalService::class, function ($app) {
            return new PurchaseRequestApprovalService();
        });
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        // Email audit trail for purchase-request notifications (email_logs table)
        Event::listen(NotificationSending::class, [EmailLogRecorder::class, 'sending']);
        Event::listen(NotificationSent::class, [EmailLogRecorder::class, 'sent']);
        Event::listen(NotificationFailed::class, [EmailLogRecorder::class, 'failed']);
    }
}
