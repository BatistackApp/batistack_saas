<?php

use App\Models\Core\Tenants;
use App\Models\Fleet\Vehicle;
use App\Models\Fleet\VehicleMaintenancePlan;
use App\Models\User;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('vehicle_maintenances', function (Blueprint $table) {
            $table->id();
            $table->foreignIdFor(Tenants::class)->constrained()->cascadeOnDelete();
            $table->foreignIdFor(Vehicle::class)->constrained()->cascadeOnDelete();
            $table->foreignIdFor(VehicleMaintenancePlan::class)->nullable()->constrained()->nullOnDelete();
            $table->foreignIdFor(User::class, 'reported_by')->nullable()->constrained();

            $table->string('internal_reference'); // Unique reference for the maintenance record (e.g., "MTN-2026-0001")
            $table->string('technician_name')->nullable(); // Interne ou Prestataire ext.

            $table->string('maintenance_type');
            $table->string('maintenance_status');

            $table->text('description')->nullable(); // Description de la panne ou de l'intervention
            $table->text('resolution_notes')->nullable();

            $table->decimal('odometer_reading', 15, 2)->nullable();
            $table->decimal('hours_reading', 15, 2)->nullable();

            $table->decimal('cost_parts', 12, 2)->default(0);
            $table->decimal('cost_labor', 12, 2)->default(0);
            $table->decimal('total_cost', 12, 2)->virtualAs('cost_parts + cost_labor');

            $table->dateTime('reported_at')->useCurrent();
            $table->dateTime('scheduled_at')->nullable();
            $table->dateTime('started_at')->nullable();
            $table->dateTime('completed_at')->nullable();

            $table->integer('downtime_hours')->nullable(); // Temps d'immobilisation calculé

            $table->timestamps();
            $table->softDeletes();

            $table->unique(['tenants_id', 'internal_reference']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('vehicle_maintenances');
    }
};
