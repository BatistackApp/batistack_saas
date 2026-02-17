<?php

use App\Models\Articles\Article;
use App\Models\Articles\Ouvrage;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('ouvrage_article', function (Blueprint $table) {
            $table->id();
            $table->foreignIdFor(Ouvrage::class)->constrained()->cascadeOnDelete();
            $table->foreignIdFor(Article::class)->constrained()->cascadeOnDelete();

            $table->decimal('quantity_needed', 15, 4);
            $table->decimal('wastage_factor_pct', 5, 2)->default(5);

            $table->timestamps();

            $table->unique(['ouvrage_id', 'article_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('ouvrage_article');
    }
};
