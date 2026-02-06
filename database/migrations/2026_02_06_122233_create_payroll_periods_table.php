<?php

use App\Models\Core\Tenants;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('payroll_periods', function (Blueprint $table) {
            $table->id();
            $table->foreignIdFor(Tenants::class)->constrained()->cascadeOnDelete();
            $table->string('name');
            $table->date('start_date');
            $table->date('end_date');
            $table->string('status')->default(\App\Enums\Payroll\PayrollStatus::Draft->value);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('payroll_periods');
    }
};
