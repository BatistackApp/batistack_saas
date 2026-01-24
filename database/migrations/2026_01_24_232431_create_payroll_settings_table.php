<?php

use App\Models\Core\Tenant;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('payroll_settings', function (Blueprint $table) {
            $table->id();
            $table->foreignIdFor(Tenant::class)->constrained()->cascadeOnDelete();
            $table->string('default_export_format')->default(\Filament\Actions\Exports\Enums\ExportFormat::Csv->value);
            $table->decimal('social_contribution_rate', 5, 2)->default(42);
            $table->boolean('auto_validate_payroll')->default(false);
            $table->boolean('auto_export_payroll')->default(false);
            $table->json('custom_fields')->nullable();
            $table->timestamps();
            $table->softDeletes();

            $table->unique('tenant_id');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('payroll_settings');
    }
};
