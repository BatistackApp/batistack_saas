<?php

use App\Models\Core\Tenants;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('billing_histories', function (Blueprint $table) {
            $table->id();
            $table->foreignIdFor(Tenants::class);
            $table->string('event_type');
            $table->string('old_plan_id')->nullable();
            $table->string('new_plan_id')->nullable();
            $table->decimal('amount_charged', 10, 2)->nullable();
            $table->string('currency')->default('EUR');
            $table->text('description')->nullable();
            $table->string('stripe_subscription_id')->nullable();
            $table->string('stripe_invoice_id')->nullable();
            $table->json('metadata')->nullable();
            $table->timestamps();

            $table->index(['tenants_id', 'event_type']);
            $table->index(['created_at']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('billing_histories');
    }
};
