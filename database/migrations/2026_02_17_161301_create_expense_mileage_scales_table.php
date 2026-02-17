<?php

use App\Models\Core\Tenants;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('expense_mileage_scales', function (Blueprint $table) {
            $table->id();
            $table->foreignIdFor(Tenants::class)->constrained()->cascadeOnDelete();
            $table->integer('vehicle_power')->comment('Puissance Fiscal');
            $table->decimal('rate_per_km', 8, 4)->comment('Tarif au KM (ex: 0.6030)');
            $table->year('active_year');
            $table->timestamps();

            $table->unique(['tenants_id', 'vehicle_power', 'active_year'], 'idx_mileage_tenant_power_year');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('expense_mileage_scales');
    }
};
