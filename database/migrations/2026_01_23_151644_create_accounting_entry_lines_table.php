<?php

use App\Models\Accounting\AccountingAccounts;
use App\Models\Accounting\AccountingEntry;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('accounting_entry_lines', function (Blueprint $table) {
            $table->id();
            $table->foreignIdFor(AccountingEntry::class)->constrained()->cascadeOnDelete();
            $table->foreignIdFor(AccountingAccounts::class)->constrained()->cascadeOnDelete();
            $table->decimal('debit', 14, 2)->default(0);
            $table->decimal('credit', 14, 2)->default(0);
            $table->string('description')->nullable();
            $table->string('analytical_code')->nullable(); // Code analytique
            $table->timestamps();

            $table->index('accounting_entry_id');
            $table->index('accounting_accounts_id');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('accounting_entry_lines');
    }
};
