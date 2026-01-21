<?php

namespace App\Providers;

use App\Filament\Responses\CustomLoginResponse;
use App\Services\PurchaseRequestApprovalService;
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
        //
    }
}
