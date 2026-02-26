<?php

use App\Models\Core\Tenants;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('payroll_contribution_templates', function (Blueprint $table) {
            $table->id();
            $table->foreignIdFor(Tenants::class)->constrained()->cascadeOnDelete();
            $table->string('label')->comment('Ex: URSSAF Vieillesse, Retraite PRO BTP');
            $table->string('code')->nullable()->comment('Code interne ou comptable');
            $table->decimal('employee_rate', 8, 4)->default(0);
            $table->decimal('employer_rate', 8, 4)->default(0);
            $table->string('applicable_to')->default('ouvrier')->comment('ouvrier, etam, cadre');
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('payroll_contribution_templates');
    }
};
