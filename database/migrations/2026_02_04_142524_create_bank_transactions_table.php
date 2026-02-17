<?php

use App\Models\Banque\BankAccount;
use App\Models\Core\Tenants;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('bank_transactions', function (Blueprint $table) {
            $table->id();
            $table->foreignIdFor(Tenants::class)->constrained()->cascadeOnDelete();
            $table->foreignIdFor(BankAccount::class)->constrained()->cascadeOnDelete();

            $table->date('value_date')->index();
            $table->string('label');
            $table->decimal('amount', 15, 2);
            $table->string('type');

            $table->string('external_id')->nullable()->comment('ID Bridge pour éviter doublons');
            $table->string('import_hash')->nullable()->index();

            $table->boolean('is_reconciled')->default(false);
            $table->json('raw_metadata')->nullable()->comment('Données brutes reçues de l\'API');
            $table->timestamps();

            $table->unique(['tenants_id', 'external_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('bank_transactions');
    }
};
