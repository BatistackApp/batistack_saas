<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        // 1. Mise à jour des catégories pour la TVA par défaut
        Schema::table('expense_categories', function (Blueprint $table) {
            $table->decimal('default_tax_rate', 5, 2)->default(20.00)->after('requires_distance');
        });

        // 2. Mise à jour des rapports pour le workflow de validation
        Schema::table('expense_reports', function (Blueprint $table) {
            $table->string('rejection_reason')->nullable()->after('status');
        });

        // 3. Mise à jour des items pour la précision des IK et de l'analytique
        Schema::table('expense_items', function (Blueprint $table) {
            // Champs spécifiques aux Indemnités Kilométriques
            $table->decimal('distance_km', 8, 2)->nullable()->after('description');
            $table->integer('vehicle_power')->nullable()->after('distance_km'); // Chevaux fiscaux
            $table->string('start_location')->nullable()->after('vehicle_power');
            $table->string('end_location')->nullable()->after('start_location');

            // Pour le suivi de refacturation projet
            $table->boolean('is_billable')->default(true)->after('amount_ttc');
        });
    }

    public function down(): void
    {
        Schema::table('expense_categories', function (Blueprint $table) {
            $table->dropColumn('default_tax_rate');
        });

        Schema::table('expense_reports', function (Blueprint $table) {
            $table->dropColumn('rejection_reason');
        });

        Schema::table('expense_items', function (Blueprint $table) {
            $table->dropColumn(['distance_km', 'vehicle_power', 'start_location', 'end_location', 'is_billable']);
        });
    }
};
