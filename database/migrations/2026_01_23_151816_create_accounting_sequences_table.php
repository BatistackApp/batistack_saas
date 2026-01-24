<?php

use App\Models\Accounting\AccountingJournal;
use App\Models\Core\Tenant;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('accounting_sequences', function (Blueprint $table) {
            $table->id();
            $table->foreignIdFor(Tenant::class)->constrained()->cascadeOnDelete();
            $table->foreignIdFor(AccountingJournal::class)->constrained()->cascadeOnDelete();
            $table->integer('year');
            $table->integer('next_number')->default(1);
            $table->timestamps();

            $table->unique(['tenant_id', 'accounting_journal_id', 'year']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('accounting_sequences');
    }
};
