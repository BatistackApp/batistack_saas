<?php

use App\Models\Chantiers\Chantier;
use App\Models\Core\Tenant;
use App\Models\Tiers\Tiers;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('devis', function (Blueprint $table) {
            $table->id();
            $table->foreignIdFor(Tenant::class)->constrained()->cascadeOnDelete();
            $table->foreignIdFor(Tiers::class)->constrained()->cascadeOnDelete();
            $table->foreignIdFor(Chantier::class)->nullable()->constrained()->nullOnDelete();
            $table->string('number')->unique();
            $table->date('date_emission');
            $table->date('date_validite')->nullable();
            $table->decimal('montant_ht', 12, 2);
            $table->decimal('montant_tva', 12, 2);
            $table->decimal('montant_ttc', 12, 2);
            $table->string('status')->default(\App\Enums\Commerce\DocumentStatus::Draft->value);
            $table->timestamps();
            $table->softDeletes();

            $table->index('tenant_id');
            $table->index('tiers_id');
            $table->index('status');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('devis');
    }
};
