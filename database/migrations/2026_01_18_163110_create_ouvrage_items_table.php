<?php

use App\Models\Articles\Article;
use App\Models\Articles\Ouvrage;
use App\Models\Core\Tenant;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('ouvrage_items', function (Blueprint $table) {
            $table->id();
            $table->foreignIdFor(Tenant::class)->constrained()->cascadeOnDelete();
            $table->foreignIdFor(Ouvrage::class)->constrained()->cascadeOnDelete();
            $table->foreignIdFor(Article::class)->constrained()->cascadeOnDelete();
            $table->decimal('quantity', 10, 3);
            $table->string('unit_of_measure');
            $table->decimal('waste_percentage')->default(0);
            $table->integer('sort_order')->default(0);
            $table->timestamps();
            $table->softDeletes();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('ouvrage_items');
    }
};
