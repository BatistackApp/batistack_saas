<?php

use App\Models\Fleet\Vehicle;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('vehicle_consumptions', function (Blueprint $table) {
            $table->id();
            $table->foreignIdFor(Vehicle::class)->constrained()->cascadeOnDelete();
            $table->date('date');
            $table->decimal('quantity', 10, 2)->comment('Quantité de carburant en Litre');
            $table->decimal('amount_ht', 15, 2);
            $table->decimal('odometer_reading', 15, 2);
            $table->string('source')->default('manual')->comment('Source de la consommation (ex: manual, api, etc.)');
            $table->string('external_transaction_id')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('vehicle_consumptions');
    }
};
