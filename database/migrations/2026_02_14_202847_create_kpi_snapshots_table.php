<?php

use App\Models\Core\Tenants;
use App\Models\Pilotage\KpiIndicator;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('kpi_snapshots', function (Blueprint $table) {
            $table->id();
            $table->ulid('ulid')->unique();
            $table->foreignIdFor(KpiIndicator::class)->constrained()->cascadeOnDelete();
            $table->foreignIdFor(Tenants::class)->constrained()->cascadeOnDelete();
            $table->decimal('value', 20, 4);
            $table->dateTime('measured_at');
            $table->nullableMorphs('context'); // Pour filtrer par Chantier, Employé, etc.
            $table->timestamps();
            $table->softDeletes();

            $table->index(['tenants_id', 'kpi_indicator_id', 'measured_at']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('kpi_snapshots');
    }
};
