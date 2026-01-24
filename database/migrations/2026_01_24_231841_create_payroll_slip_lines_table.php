<?php

use App\Models\Chantiers\Chantier;
use App\Models\Payroll\PayrollSlip;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('payroll_slip_lines', function (Blueprint $table) {
            $table->id();
            $table->foreignIdFor(PayrollSlip::class)->constrained()->cascadeOnDelete();
            $table->foreignIdFor(Chantier::class)->nullable()->constrained()->nullOnDelete();
            $table->string('description');
            $table->decimal('hours_work', 10, 2)->default(0);
            $table->decimal('hours_travel', 10, 2)->default(0);
            $table->decimal('hourly_rate', 10, 2);
            $table->decimal('amount', 10, 2);
            $table->timestamps();

            $table->index('payroll_slip_id');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('payroll_slip_lines');
    }
};
