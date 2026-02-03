<?php

use App\Models\Commerce\Invoices;
use App\Models\Commerce\QuoteItem;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('invoice_items', function (Blueprint $table) {
            $table->id();
            $table->foreignIdFor(Invoices::class)->constrained()->cascadeOnDelete();
            $table->foreignIdFor(QuoteItem::class)->nullable()->constrained()->nullOnDelete();

            $table->string('label');
            $table->decimal('quantity', 15, 3);
            $table->decimal('unit_price_ht', 15, 2);
            $table->decimal('tax_rate', 5, 2)->default(20.00);

            $table->decimal('progress_percentage', 5, 2)->nullable();

            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('invoice_items');
    }
};
