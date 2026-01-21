<?php

use App\Models\Articles\Article;
use App\Models\Commerce\Commande;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('commande_lignes', function (Blueprint $table) {
            $table->id();
            $table->foreignIdFor(Commande::class)->constrained()->cascadeOnDelete();
            $table->foreignIdFor(Article::class)->nullable()->constrained()->nullOnDelete();
            $table->decimal('quantite_commande');
            $table->decimal('quantite_livre')->default(0);
            $table->decimal('prix_unitaire', 12, 2);
            $table->string('tva')->default(\App\Enums\Commerce\TaxRate::Normal->value);
            $table->decimal('montant_ht', 12, 2);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('commande_lignes');
    }
};
