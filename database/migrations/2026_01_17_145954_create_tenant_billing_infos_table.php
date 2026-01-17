<?php

use App\Models\Core\Tenant;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('tenant_billing_infos', function (Blueprint $table) {
            $table->id();
            $table->string('company_name');
            $table->string('billing_email');
            $table->string('billing_address');
            $table->string('postal_code');
            $table->string('city');
            $table->string('country');
            $table->string('vat_number')->nullable();
            $table->string('phone')->nullable();
            $table->foreignIdFor(Tenant::class)->constrained()->cascadeOnDelete();
            $table->timestamps();

            $table->unique(['tenant_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('tenant_billing_infos');
    }
};
