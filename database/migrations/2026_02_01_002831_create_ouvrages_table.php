<?php

use App\Models\Core\Tenants;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('ouvrages', function (Blueprint $table) {
            $table->id();
            $table->foreignIdFor(Tenants::class)->constrained()->cascadeOnDelete();

            $table->string('sku')->unique()->comment("Référence technique de l\'ouvrage");
            $table->string('name');
            $table->text('description')->nullable();
            $table->string('unit')->default(\App\Enums\Articles\ArticleUnit::Unit->value);

            $table->boolean('is_active')->default(true);
            $table->timestamps();
            $table->softDeletes();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('ouvrages');
    }
};
