<?php

use App\Models\Core\Tenants;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('vehicle_maintenance_plans', function (Blueprint $table) {
            $table->id();
            $table->foreignIdFor(Tenants::class)->constrained()->cascadeOnDelete();
            $table->string('name'); // ex: "Révision des 500h" ou "Vidange annuelle"
            $table->string('vehicle_type');
            $table->integer('interval_km')->nullable();
            $table->integer('interval_hours')->nullable();
            $table->integer('interval_month')->nullable();
            $table->json('operations')->nullable(); // Liste des tâches à effectuer
            $table->boolean('is_active')->default(true);
            $table->timestamps();
            $table->softDeletes();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('vehicle_maintenance_plans');
    }
};
