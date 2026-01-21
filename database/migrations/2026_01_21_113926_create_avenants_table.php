<?php

use App\Models\Commerce\Commande;
use App\Models\Core\Tenant;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('avenants', function (Blueprint $table) {
            $table->id();
            $table->foreignIdFor(Tenant::class)->constrained()->cascadeOnDelete();
            $table->foreignIdFor(Commande::class)->constrained()->cascadeOnDelete();
            $table->string('number')->unique();
            $table->date('date_avenant')->default(now());
            $table->text('description')->nullable();
            $table->decimal('montant_ht', 12, 2);
            $table->decimal('montant_tva', 12, 2);
            $table->decimal('montant_ttc', 12, 2);
            $table->string('status')->default(\App\Enums\Commerce\DocumentStatus::Draft->value);
            $table->timestamps();
            $table->softDeletes();

            $table->index('tenant_id');
            $table->index('commande_id');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('avenants');
    }
};
