<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('interventions', function (Blueprint $table) {
            // Rapport et Signature (Recommandation 1)
            $table->text('report_notes')->nullable()->comment('Note technique terrain');
            $table->text('completed_notes')->nullable()->comment('Commentaires finaux');
            $table->longText('client_signature')->nullable()->comment('Signature en Base64');
            $table->string('client_name')->nullable();

            // Analyse financière (Recommandation 4 - Distinction des coûts)
            $table->decimal('material_cost_ht', 15, 2)->default(0)->comment('Coût de revient pièces');
            $table->decimal('labor_cost_ht', 15, 2)->default(0)->comment('Coût de revient main d\'oeuvre');

            $table->dateTime('started_at')->nullable();
            $table->dateTime('completed_at')->nullable();
        });

        Schema::table('employees', function (Blueprint $table) {
            $table->foreignIdFor(\App\Models\Articles\Warehouse::class, 'default_warehouse_id')->nullable()->constrained()->nullOnDelete();
        });
    }

    public function down(): void
    {
        Schema::table('interventions', function (Blueprint $table) {
            $table->dropColumn([
                'report_notes',
                'completed_notes',
                'client_signature',
                'client_name',
                'material_cost_ht',
                'labor_cost_ht',
                'started_at',
                'completed_at',
            ]);
        });

        Schema::table('employees', function (Blueprint $table) {
            $table->dropColumn('default_warehouse_id');
        });
    }
};
