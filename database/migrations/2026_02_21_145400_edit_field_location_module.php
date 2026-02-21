<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('rental_contracts', function (Blueprint $table) {
            $table->timestamp('off_hire_requested_at')->nullable()->after('end_date_planned');
            $table->decimal('delivery_cost_ht', 12, 2)->default(0)->after('notes');
            $table->decimal('return_cost_ht', 12, 2)->default(0)->after('delivery_cost_ht');
            $table->decimal('cleaning_fees_ht', 12, 2)->default(0)->after('return_cost_ht');
            $table->decimal('refuel_fees_ht', 12, 2)->default(0)->after('cleaning_fees_ht');
            $table->integer('extension_count')->default(0)->after('label');
        });

        Schema::table('rental_inspections', function (Blueprint $table) {
            $table->text('client_signature')->nullable()->comment('Base64 signature du chef de chantier');
            $table->text('provider_signature')->nullable()->comment('Base64 signature du loueur');
        });
    }

    public function down(): void
    {
        Schema::table('rental_contracts', function (Blueprint $table) {
            $table->dropColumn('off_hire_requested_at');
            $table->dropColumn('delivery_cost_ht');
            $table->dropColumn('return_cost_ht');
            $table->dropColumn('cleaning_fees_ht');
            $table->dropColumn('refuel_fees_ht');
            $table->dropColumn('extension_count');
        });

        Schema::table('rental_inspections', function (Blueprint $table) {
            $table->dropColumn('client_signature');
            $table->dropColumn('provider_signature');
        });
    }
};
