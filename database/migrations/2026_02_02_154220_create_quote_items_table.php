<?php

use App\Models\Articles\Article;
use App\Models\Commerce\Quote;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('quote_items', function (Blueprint $table) {
            $table->id();
            $table->foreignIdFor(Quote::class)->constrained()->cascadeOnDelete();
            $table->foreignIdFor(Article::class)->nullable()->constrained()->nullOnDelete();
            $table->string('label');
            $table->decimal('quantity', 15, 3);
            $table->decimal('unit_price_ht', 15, 2);
            $table->decimal('tax_rate', 5, 2)->default(20.00);
            $table->integer('order')->default(0);

            $table->boolean('is_cost_outdated')->default(false);
            $table->decimal('cost_variation_pct')->default(0);
            $table->decimal('last_known_cost_ht', 15, 2)->nullable();

            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('quote_items');
    }
};
