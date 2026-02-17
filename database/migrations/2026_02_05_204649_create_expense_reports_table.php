<?php

use App\Models\Core\Tenants;
use App\Models\User;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('expense_reports', function (Blueprint $table) {
            $table->id();
            $table->foreignIdFor(Tenants::class)->constrained()->cascadeOnDelete();
            $table->foreignIdFor(User::class)->constrained();
            $table->string('label');
            $table->decimal('amount_ht', 12, 2)->default(0);
            $table->decimal('amount_tva', 12, 2)->default(0);
            $table->decimal('amount_ttc', 12, 2)->default(0);
            $table->string('status')->default(\App\Enums\Expense\ExpenseStatus::Draft->value);
            $table->timestamp('submitted_at')->nullable();
            $table->timestamp('validated_at')->nullable();
            $table->foreignIdFor(User::class, 'validated_by')->nullable()->constrained();
            $table->timestamps();
            $table->softDeletes();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('expense_reports');
    }
};
