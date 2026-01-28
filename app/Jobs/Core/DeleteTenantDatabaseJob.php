<?php

namespace App\Jobs\Core;

use DB;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Log;

class DeleteTenantDatabaseJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public int $timeout = 300; // 5 minutes
    public int $tries = 1;

    public function __construct(
        private string $databaseName,
    ) {}

    public function handle(): void
    {
        try {
            Log::critical("Deleting database schema: {$this->databaseName}");

            DB::statement("DROP SCHEMA IF EXISTS `{$this->databaseName}`");

            Log::info("Database schema deleted: {$this->databaseName}");
        } catch (\Exception $e) {
            Log::error("Failed to delete database schema: {$this->databaseName}", [
                'error' => $e->getMessage(),
            ]);

            throw $e;
        }
    }
}
