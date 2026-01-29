<?php

use App\Models\Core\Tenants;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('tiers', function (Blueprint $table) {
            $table->id();
            $table->foreignIdFor(Tenants::class)->constrained()->cascadeOnDelete();
            $table->string('code_tiers')->unique(); // Auto-généré
            $table->string('type_entite'); // 'personne_physique' ou 'personne_morale'

            $table->string('raison_social')->nullable();
            $table->string('nom')->nullable();
            $table->string('prenom')->nullable();

            $table->string('adresse')->nullable();
            $table->string('code_postal', 5)->nullable();
            $table->string('ville')->nullable();
            $table->string('pays')->default('FR');
            $table->string('telephone')->nullable();
            $table->string('email')->nullable();
            $table->string('site_web')->nullable();

            $table->string('siret', 14)->nullable()->index();
            $table->string('numero_tva')->nullable();
            $table->string('code_naf', 10)->nullable();

            $table->string('iban')->nullable();
            $table->string('bic')->nullable();
            $table->integer('delai_paiement_days')->default(30);
            $table->string('condition_reglement')->nullable();

            $table->string('status')->default(\App\Enums\Tiers\TierStatus::Active->value);

            $table->json('metadata')->nullable();
            $table->timestamps();
            $table->softDeletes();

            $table->index(['tenants_id', 'code_tiers']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('tiers');
    }
};
