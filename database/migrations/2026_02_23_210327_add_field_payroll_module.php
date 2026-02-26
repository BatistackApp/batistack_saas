<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('payslip_lines', function (Blueprint $table) {
            $table->boolean('is_taxable')->default(true)->after('type');
            $table->boolean('is_net_deduction')->default(false)->after('is_taxable')->comment('Pour les acomptes ou saisies sur salaire');
        });

        Schema::table('employees', function (Blueprint $table) {
            $table->string('social_security_number')->nullable()->after('last_name');
            $table->string('btp_travel_zone')->nullable()->comment('Zone 1 à 5');
            $table->boolean('is_long_distance_travel')->default(false);

            $table->string('btp_level')->nullable()->comment('Niveau: I à IV');
            $table->string('btp_position')->nullable()->comment('Position: 1, 2... ou Coefficient');
            $table->decimal('monthly_base_salary', 12, 2)->default(0);
        });
    }

    public function down(): void
    {
        Schema::table('payslip_lines', function (Blueprint $table) {
            $table->dropColumn(['is_taxable', 'is_net_deduction']);
        });

        Schema::dropIfExists('payroll_scales');

        Schema::table('employees', function (Blueprint $table) {
            $table->dropColumn([
                'social_security_number',
                'btp_travel_zone',
                'is_long_distance_travel',
                'btp_level',
                'btp_position',
                'monthly_base_salary',
            ]);
        });
    }
};
