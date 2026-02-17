<?php

use App\Models\Commerce\PurchaseOrderItem;
use App\Models\User;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('purchase_order_receptions', function (Blueprint $table) {
            $table->id();
            $table->foreignIdFor(PurchaseOrderItem::class)->constrained()->cascadeOnDelete();
            $table->decimal('quantity', 15, 3);
            $table->string('delivery_note_ref')->nullable()->comment('Référence du BL Fournisseur');
            $table->date('received_at');
            $table->foreignIdFor(User::class, 'created_by')->constrained();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('purchase_order_receptions');
    }
};
