<?php

use App\Models\Payroll\PayrollSlip;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('payroll_slip_deductions', function (Blueprint $table) {
            $table->id();
            $table->foreignIdFor(PayrollSlip::class)->constrained()->cascadeOnDelete();
            $table->string('type')->default(\App\Enums\Payroll\PayrollDeductionType::Other);
            $table->string('description');
            $table->decimal('amount', 10, 2);
            $table->text('reason')->nullable();
            $table->timestamps();

            $table->index('payroll_slip_id');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('payroll_slip_deductions');
    }
};
