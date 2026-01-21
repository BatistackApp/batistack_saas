<?php

use App\Models\Commerce\Facture;
use App\Models\Core\Tenant;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('avoirs', function (Blueprint $table) {
            $table->id();
            $table->foreignIdFor(Tenant::class);
            $table->foreignIdFor(Facture::class)->nullable();
            $table->string('number')->unique();
            $table->date('date_avoir');
            $table->string('motif');
            $table->decimal('montant_ht');
            $table->decimal('montant_tva');
            $table->decimal('montant_ttc');
            $table->string('status');
            $table->timestamps();
            $table->softDeletes();

            $table->index('tenant_id');
            $table->index('facture_id');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('avoirs');
    }
};
