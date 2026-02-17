<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('accounting_entry_lines', function (Blueprint $table) {
            $table->foreignIdFor(\App\Models\Core\Tenants::class, 'tenants_id')->after('id')->constrained()->cascadeOnDelete();
        });
    }

    public function down(): void
    {
        Schema::table('accounting_entry_lines', function (Blueprint $table) {
            $table->dropColumn('tenants_id');
        });
    }
};
