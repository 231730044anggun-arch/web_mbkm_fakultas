<?php

use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;

Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote');

app(\Illuminate\Contracts\Console\Kernel::class)->registerCommand(app(\App\Console\Commands\ClearDemoData::class));
