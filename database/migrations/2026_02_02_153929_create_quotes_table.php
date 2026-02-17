<?php

use App\Models\Core\Tenants;
use App\Models\Projects\Project;
use App\Models\Tiers\Tiers;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('quotes', function (Blueprint $table) {
            $table->id();
            $table->foreignIdFor(Tenants::class)->constrained()->cascadeOnDelete();
            $table->foreignIdFor(Tiers::class, 'customer_id')->constrained()->cascadeOnDelete();
            $table->foreignIdFor(Project::class)->nullable()->constrained()->nullOnDelete();
            $table->string('reference');
            $table->string('status')->default(\App\Enums\Commerce\QuoteStatus::Draft->value);
            $table->decimal('total_ht', 15, 2)->default(0);
            $table->decimal('total_tva', 15, 2)->default(0);
            $table->decimal('total_ttc', 15, 2)->default(0);
            $table->date('valid_until')->nullable();
            $table->timestamps();
            $table->softDeletes();

            $table->unique(['tenants_id', 'reference']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('quotes');
    }
};
