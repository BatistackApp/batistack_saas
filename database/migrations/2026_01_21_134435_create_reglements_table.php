<?php

use App\Models\Commerce\Facture;
use App\Models\Core\Tenant;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('reglements', function (Blueprint $table) {
            $table->id();
            $table->foreignIdFor(Tenant::class)->constrained()->cascadeOnDelete();
            $table->foreignIdFor(Facture::class)->constrained()->cascadeOnDelete();
            $table->string('reference_paiement')->nullable();
            $table->date('date_paiement')->default(now());
            $table->decimal('montant', 12, 2);
            $table->string('type_paiement')->default(\App\Enums\Commerce\TypePaiement::VIREMENT->value);
            $table->text('notes')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('reglements');
    }
};
