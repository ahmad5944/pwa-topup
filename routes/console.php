<?php

use App\Jobs\ReconcileDepositsJob;
use App\Jobs\ReconcilePendingTransactionsJob;
use App\Jobs\SyncProviderPricesJob;
use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Schedule;

Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote');

Schedule::job(new ReconcilePendingTransactionsJob)->everyFiveMinutes();
Schedule::job(new SyncProviderPricesJob)->hourly();
Schedule::job(new ReconcileDepositsJob)->everyFifteenMinutes();
