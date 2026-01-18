<?php

use App\Models\Articles\ArticleCategory;
use App\Models\Core\Tenant;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('articles', function (Blueprint $table) {
            $table->id();
            $table->foreignIdFor(Tenant::class)->constrained()->cascadeOnDelete();
            $table->foreignIdFor(ArticleCategory::class)->nullable()->constrained()->cascadeOnDelete();
            $table->string('name');
            $table->string('slug')->unique();
            $table->text('description')->nullable();
            $table->string('code')->unique();
            $table->string('barcode')->nullable()->unique();
            $table->string('sku')->nullable();
            $table->string('type')->default(\App\Enums\Articles\ArticleType::Produit);
            $table->string('unit_of_measure')->default(\App\Enums\Articles\UnitOfMeasure::Unitaire);
            $table->decimal('weight_kg', 8, 3)->nullable();
            $table->decimal('volume_m3', 10, 4)->nullable();
            $table->decimal('purchase_price', 10, 2)->nullable();
            $table->decimal('selling_price', 10, 2)->nullable();
            $table->decimal('margin_percentage', 5, 2)->nullable();
            $table->string('vat_rate')->default('20');
            $table->string('external_reference')->nullable();
            $table->boolean('is_active')->default(true);
            $table->boolean('requires_lot_tracking')->default(false);
            $table->boolean('requires_serial_number')->default(false);
            $table->timestamps();
            $table->softDeletes();

            $table->unique(['tenant_id', 'code']);
            $table->unique(['tenant_id', 'slug']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('articles');
    }
};
