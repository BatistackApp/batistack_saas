<?php

use App\Models\Core\Tenant;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('invoices', function (Blueprint $table) {
            $table->id();
            $table->string('stripe_invoice_id')->nullable()->unique();
            $table->decimal('amount');
            $table->string('status');
            $table->timestamp('billing_period_start');
            $table->timestamp('billing_period_end');
            $table->timestamp('issued_at');
            $table->timestamp('due_at');
            $table->timestamp('paid_at');
            $table->foreignIdFor(Tenant::class)->constrained()->cascadeOnDelete();
            $table->timestamps();

            $table->index('tenant_id');
            $table->index('status');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('invoices');
    }
};
