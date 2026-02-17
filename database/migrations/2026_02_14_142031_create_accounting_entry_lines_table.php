<?php

use App\Models\Accounting\AccountingEntry;
use App\Models\Accounting\ChartOfAccount;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('accounting_entry_lines', function (Blueprint $table) {
            $table->id();
            $table->ulid('ulid')->unique();
            $table->foreignIdFor(AccountingEntry::class)->constrained()->cascadeOnDelete();
            $table->foreignIdFor(ChartOfAccount::class)->constrained()->cascadeOnDelete();
            $table->foreignIdFor(\App\Models\Projects\Project::class)->nullable()->constrained()->nullOnDelete();
            $table->foreignIdFor(\App\Models\Projects\ProjectPhase::class)->nullable()->constrained()->nullOnDelete();
            $table->decimal('debit', 15, 4)->default(0);
            $table->decimal('credit', 15, 4)->default(0);
            $table->string('description')->nullable();
            $table->integer('line_order')->default(0);
            $table->timestamps();

            $table->index(['accounting_entry_id']);
            $table->index(['chart_of_account_id']);
            $table->index(['project_id', 'project_phase_id'], 'idx_ccounting_analytics');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('accounting_entry_lines');
    }
};
