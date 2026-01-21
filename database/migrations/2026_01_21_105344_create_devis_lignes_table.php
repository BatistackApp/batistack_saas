<?php

use App\Models\Articles\Article;
use App\Models\Commerce\Devis;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('devis_lignes', function (Blueprint $table) {
            $table->id();
            $table->foreignIdFor(Devis::class)->constrained()->cascadeOnDelete();
            $table->foreignIdFor(Article::class)->nullable()->constrained()->nullOnDelete();
            $table->text('description');
            $table->decimal('quantite');
            $table->decimal('prix_unitaire', 12, 2);
            $table->string('tva')->default(\App\Enums\Commerce\TaxRate::Normal->value);
            $table->decimal('montant_ht', 12, 2);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('devis_lignes');
    }
};
