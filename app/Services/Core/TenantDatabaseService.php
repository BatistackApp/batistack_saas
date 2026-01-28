<?php

namespace App\Services\Core;

use Illuminate\Support\Facades\DB;
use Log;

class TenantDatabaseService
{
    public function createSchema(string $databaseName): bool
    {
        try {
            DB::statement("CREATE SCHEMA IF NOT EXISTS `{$databaseName}` CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci");

            Log::info("Database schema created: {$databaseName}");

            return true;
        } catch (\Exception $e) {
            Log::error("Failed to create database schema: {$databaseName}", [
                'error' => $e->getMessage(),
            ]);

            throw $e;
        }
    }

    public function migrateSchema(string $databaseName): bool
    {
        try {
            // Dispatch job pour éviter le blocage
            \Illuminate\Support\Facades\Bus::dispatch(
                new \App\Jobs\Core\MigrateTenantDatabaseJob($databaseName)
            );

            Log::info("Migration job dispatched for schema: {$databaseName}");

            return true;
        } catch (\Exception $e) {
            Log::error("Failed to migrate database schema: {$databaseName}", [
                'error' => $e->getMessage(),
            ]);

            throw $e;
        }
    }

    public function seedTenantData(object $tenant): void
    {
        // Exécution via job asynchrone
        \Illuminate\Support\Facades\Bus::dispatch(
            new \App\Jobs\Core\SeedTenantDataJob($tenant->id, $tenant->database)
        );
    }
}
