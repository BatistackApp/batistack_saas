<?php

use App\Models\Chantiers\Chantier;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('chantier_budgets', function (Blueprint $table) {
            $table->id();
            $table->foreignIdFor(Chantier::class)->constrained()->cascadeOnDelete();
            $table->string('category');
            $table->decimal('planned_amount', 15, 2);
            $table->timestamps();

            $table->index(['chantier_id', 'category']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('chantier_budgets');
    }
};
