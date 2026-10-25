<?php

use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Schedule;

Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote');

Schedule::command('orders:sync-pending')->everyFifteenMinutes()->withoutOverlapping();

// Shared hosting has no queue daemon: drain the database queue (verification / reset emails) once a minute.
Schedule::command('queue:work --stop-when-empty --tries=3 --max-time=50')->everyMinute()->withoutOverlapping(5);

Schedule::command('badges:award')->dailyAt('02:00')->withoutOverlapping();

Schedule::command('certificates:prune-files')->dailyAt('02:30')->withoutOverlapping();
