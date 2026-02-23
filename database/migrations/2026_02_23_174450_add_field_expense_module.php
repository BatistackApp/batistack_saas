<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('expense_mileage_scales', function (Blueprint $table) {
            $table->integer('min_km')->default(0)->after('vehicle_power');
            $table->integer('max_km')->nullable()->after('min_km');

            // On ajoute la composante fixe (le "+ B" dans la formule Ax + B)
            $table->decimal('fixed_amount', 12, 2)->default(0)->after('rate_per_km');

            // Optionnel : Type de véhicule (Auto, Moto, Cyclo) si besoin de précision
            $table->string('vehicle_type')->default('car')->after('active_year');
        });
    }

    public function down(): void
    {
        Schema::table('expense_mileage_scales', function (Blueprint $table) {
            $table->dropColumn(['min_km', 'max_km', 'fixed_amount', 'vehicle_type']);
        });
    }
};
