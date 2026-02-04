<?php

use App\Models\Banque\BankTransaction;
use App\Models\Commerce\Invoices;
use App\Models\Core\Tenants;
use App\Models\User;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('payments', function (Blueprint $table) {
            $table->id();
            $table->foreignIdFor(Tenants::class)->constrained()->cascadeOnDelete();
            $table->foreignIdFor(BankTransaction::class)->nullable()->constrained()->nullOnDelete();
            $table->foreignIdFor(Invoices::class)->constrained()->cascadeOnDelete();

            $table->decimal('amount', 15, 2);
            $table->date('payment_date');
            $table->string('payment_method');
            $table->string('reference')->nullable()->comment('N° Chèque ou Réf Virement');

            $table->foreignIdFor(User::class, 'created_by')->constrained();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('payments');
    }
};
