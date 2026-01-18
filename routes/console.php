<?php

use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;

Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote');

Schedule::job(new \App\Jobs\Articles\CheckLowStockJob())
    ->dailyAt('06:00')
    ->description("Check for low stock articles");

Schedule::job(new \App\Jobs\Articles\ArchiveUnusedArticleJob())
    ->monthly()
    ->description("Archive unused articles");
