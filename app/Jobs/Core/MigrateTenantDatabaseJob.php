<?php

namespace App\Jobs\Core;

use Artisan;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Log;

class MigrateTenantDatabaseJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public int $timeout = 300; // 5 minutes
    public int $tries = 3;

    public function __construct(
        private string $databaseName,
    ) {}

    public function handle(): void
    {
        try {
            Log::info("Starting migration for schema: {$this->databaseName}");

            // Exécuter les migrations spécifiques au tenant
            Artisan::call('migrate', [
                '--database' => $this->databaseName,
                '--path' => 'database/migrations/tenant',
            ]);

            Log::info("Migration completed for schema: {$this->databaseName}");
        } catch (\Exception $e) {
            Log::error("Migration failed for schema: {$this->databaseName}", [
                'error' => $e->getMessage(),
            ]);

            throw $e;
        }
    }
}
