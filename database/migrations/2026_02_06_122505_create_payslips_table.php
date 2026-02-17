<?php

use App\Models\Core\Tenants;
use App\Models\HR\Employee;
use App\Models\Payroll\PayrollPeriod;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('payslips', function (Blueprint $table) {
            $table->id();
            $table->foreignIdFor(Tenants::class)->constrained()->cascadeOnDelete();
            $table->foreignIdFor(PayrollPeriod::class)->constrained()->cascadeOnDelete();
            $table->foreignIdFor(Employee::class)->constrained()->cascadeOnDelete();
            $table->decimal('gross_amount', 12, 2)->default(0);
            $table->decimal('net_social_amount', 12, 2)->default(0);
            $table->decimal('net_to_pay', 12, 2)->default(0);
            $table->decimal('pas_rate', 5, 2)->default(0);
            $table->decimal('pas_amount', 12, 2)->default(0);
            $table->string('status')->default(\App\Enums\Payroll\PayrollStatus::Draft->value);
            $table->json('metadata')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('payslips');
    }
};
