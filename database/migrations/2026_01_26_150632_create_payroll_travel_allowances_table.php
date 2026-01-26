<?php

use App\Models\Payroll\PayrollSlip;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('payroll_travel_allowances', function (Blueprint $table) {
            $table->id();
            $table->uuid('uuid')->unique();
            $table->foreignIdFor(PayrollSlip::class)->constrained()->cascadeOnDelete();
            $table->decimal('amount', 10, 2);
            $table->decimal('distance_km', 10, 2)->nullable();
            $table->text('description')->nullable();
            $table->timestamps();

            $table->index('payroll_slip_id');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('payroll_travel_allowances');
    }
};
