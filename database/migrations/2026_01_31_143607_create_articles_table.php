<?php

use App\Models\Articles\ArticleCategory;
use App\Models\Core\Tenants;
use App\Models\Tiers\Tiers;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('articles', function (Blueprint $table) {
            $table->id();
            $table->foreignIdFor(Tenants::class)->constrained()->cascadeOnDelete();
            $table->foreignIdFor(ArticleCategory::class, 'category_id')->nullable()->constrained('article_categories')->nullOnDelete();
            $table->foreignIdFor(Tiers::class, 'default_supplier_id')->nullable()->constrained('tiers')->nullOnDelete();

            $table->string('sku')->comment('Référence interne');
            $table->string('name');
            $table->text('description')->nullable();
            $table->string('unit')->default(\App\Enums\Articles\ArticleUnit::Unit->value);
            $table->string('tracking_type')->default(\App\Enums\Articles\TrackingType::Quantity->value);

            $table->string('barcode')->nullable()->index()->comment('EAN/UPC');
            $table->string('qr_code_base')->nullable()->unique()->comment('Etiquette QR Code interne');

            $table->decimal('poids', 12, 3)->nullable(); // Poids en kg
            $table->decimal('volume', 12, 3)->nullable(); // Volume en m3

            $table->decimal('purchase_price_ht', 15, 2)->default(0)->comment('Dernier prix d\'achat HT');
            $table->decimal('cump_ht', 15, 2)->default(0)->comment('Coût Unitaire Moyen Pondéré');
            $table->decimal('sale_price_ht', 15, 2)->default(0);

            $table->decimal('min_stock', 15, 3)->default(0);
            $table->decimal('alert_stock', 15, 3)->default(0);
            $table->decimal('total_stock', 15, 3)->default(0);

            $table->timestamps();
            $table->softDeletes();

            $table->unique(['tenants_id', 'sku']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('articles');
    }
};
