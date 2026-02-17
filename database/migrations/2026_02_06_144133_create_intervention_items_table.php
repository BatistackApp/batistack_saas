<?php

use App\Models\Articles\Article;
use App\Models\Articles\ArticleSerialNumber;
use App\Models\Articles\Ouvrage;
use App\Models\Intervention\Intervention;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('intervention_items', function (Blueprint $table) {
            $table->id();
            $table->foreignIdFor(Intervention::class)->constrained()->cascadeOnDelete();
            $table->foreignIdFor(Article::class)->nullable()->constrained();
            $table->foreignIdFor(Ouvrage::class)->nullable()->constrained();
            $table->foreignIdFor(ArticleSerialNumber::class)->nullable()->constrained();

            $table->string('label');
            $table->decimal('quantity', 12, 3);
            $table->decimal('unit_price_ht', 15, 2); // Prix de vente (issu de Article->sale_price_ht)
            $table->decimal('unit_cost_ht', 15, 2); // Coût de revient (issu de Article->cump_ht)
            $table->decimal('tax_rate', 5, 2)->default(20.00);
            $table->decimal('total_ht', 15, 2);
            $table->boolean('is_billable')->default(true);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('intervention_items');
    }
};
