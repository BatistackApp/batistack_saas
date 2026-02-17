<?php

use App\Models\Payroll\Payslip;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('payslip_lines', function (Blueprint $table) {
            $table->id();
            $table->foreignIdFor(Payslip::class)->constrained()->cascadeOnDelete();
            $table->string('label');
            $table->decimal('base', 12, 2)->nullable();
            $table->decimal('rate', 12, 4)->nullable();
            $table->decimal('amount_gain', 12, 2)->nullable();
            $table->decimal('amount_deduction', 12, 2)->nullable();
            $table->decimal('employer_amount', 12, 2)->nullable();
            $table->string('type');
            $table->integer('sort_order')->default(0);
            $table->boolean('is_manual_adjustment')->default(false);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('payslip_lines');
    }
};
