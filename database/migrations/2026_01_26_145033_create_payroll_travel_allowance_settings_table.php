<?php

use App\Models\Core\Tenant;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('payroll_travel_allowance_settings', function (Blueprint $table) {
            $table->id();
            $table->uuid('uuid')->unique();
            $table->foreignIdFor(Tenant::class)->constrained()->cascadeOnDelete();
            $table->string('type')->default(\App\Enums\Payroll\TravelAllowanceType::Forfeit->value);
            $table->decimal('rate_per_km', 5, 3)->nullable();
            $table->decimal('forfeit_amount', 10, 2)->nullable();
            $table->decimal('max_amount_per_day', 10, 2)->nullable();
            $table->boolean('is_active')->default(true);
            $table->timestamps();
            $table->softDeletes();

            $table->unique('tenant_id');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('payroll_travel_allowance_settings');
    }
};
