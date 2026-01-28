<?php

namespace App\Jobs\Core;

use Artisan;
use DB;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Log;

class SeedTenantDataJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public int $timeout = 600; // 10 minutes
    public int $tries = 2;

    public function __construct(
        private int $tenantId,
        private string $databaseName,
    ) {}

    public function handle(): void
    {
        try {
            Log::info("Starting seeding for tenant: {$this->tenantId}");

            // En test avec SQLite, ignorer les migrations de schéma tenant
            if (DB::getDriverName() === 'sqlite') {
                Log::info("Migration skipped for SQLite: {$this->databaseName}");
                return;
            }

            // Exécuter les seeders tenant-spécifiques
            Artisan::call('db:seed', [
                '--database' => $this->databaseName,
                '--class' => 'Database\\Seeders\\Tenant\\TenantSeeder',
            ]);

            Log::info("Seeding completed for tenant: {$this->tenantId}");
        } catch (\Exception $e) {
            Log::error("Seeding failed for tenant: {$this->tenantId}", [
                'error' => $e->getMessage(),
                'database' => $this->databaseName,
            ]);

            throw $e;
        }
    }
}
