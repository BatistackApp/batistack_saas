<?php

use App\Models\Expense\ExpenseCategory;
use App\Models\Expense\ExpenseReport;
use App\Models\Projects\Project;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('expense_items', function (Blueprint $table) {
            $table->id();
            $table->foreignIdFor(ExpenseReport::class)->constrained()->cascadeOnDelete();
            $table->foreignIdFor(ExpenseCategory::class)->constrained();
            $table->foreignIdFor(Project::class)->nullable()->constrained();
            $table->date('date');
            $table->string('description');
            $table->decimal('amount_ht', 12, 2);
            $table->decimal('tax_rate', 5, 2)->default(20.00);
            $table->decimal('amount_tva', 12, 2);
            $table->decimal('amount_ttc', 12, 2);
            $table->string('receipt_path')->nullable();
            $table->json('metadata')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('expense_items');
    }
};
