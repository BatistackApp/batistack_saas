<?php

use App\Models\Core\Tenant;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('tiers', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('slug')->unique();
            $table->text('description')->nullable();
            $table->string('email')->nullable();
            $table->string('phone')->nullable();
            $table->string('website')->nullable();
            $table->string('siret')->nullable()->unique();
            $table->string('vat_number')->nullable();
            $table->string('iban')->nullable();
            $table->string('bic')->nullable();
            $table->string('types')->default('[]'); // Stockage des rôles multiples
            $table->decimal('discount_percentage', 5, 2)->default(0);
            $table->integer('payment_delay_days')->default(0);
            $table->boolean('is_active')->default(true);
            $table->foreignIdFor(Tenant::class)->constrained()->cascadeOnDelete();
            $table->timestamps();
            $table->softDeletes();

            $table->index(['tenant_id', 'is_active']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('tiers');
    }
};
