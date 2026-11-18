<?php

use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Schedule;

Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote');

Schedule::command('approvals:check-overdue')
    ->dailyAt('08:00')
    ->timezone('Asia/Jakarta') // 08:00 WIB — app.timezone stays UTC so stored/displayed times don't shift
    ->withoutOverlapping();
