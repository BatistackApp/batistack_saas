<?php

use App\Models\Chantiers\Chantier;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('chantier_costs', function (Blueprint $table) {
            $table->id();
            $table->foreignIdFor(Chantier::class)->constrained()->cascadeOnDelete();
            $table->string('category')->default(\App\Enums\Chantiers\CostCategory::Other->value);
            $table->string('label');
            $table->text('description')->nullable();
            $table->decimal('amount', 15, 2);
            $table->date('cost_date');
            $table->string('reference')->nullable();
            $table->timestamps();

            $table->index(['chantier_id', 'cost_date', 'category']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('chantier_costs');
    }
};
