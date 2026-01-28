<?php

use App\Models\Payroll\PayrollSlip;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('payroll_overtimes', function (Blueprint $table) {
            $table->id();
            $table->uuid('uuid')->unique();
            $table->foreignIdFor(PayrollSlip::class)->constrained()->cascadeOnDelete();
            $table->string('type')->default(\App\Enums\Payroll\OvertimeType::Standard->value);
            $table->decimal('hours', 10, 2);
            $table->decimal('hourly_rate', 10, 2);
            $table->integer('multiplier')->default(125);
            $table->decimal('amount', 10, 2);
            $table->text('description')->nullable();
            $table->timestamps();

            $table->index("payroll_slip_id");
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('payroll_overtimes');
    }
};
