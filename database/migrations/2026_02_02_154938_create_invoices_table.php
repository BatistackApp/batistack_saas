<?php

use App\Models\Commerce\Quote;
use App\Models\Core\Tenants;
use App\Models\Projects\Project;
use App\Models\Tiers\Tiers;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('invoices', function (Blueprint $table) {
            $table->id();
            $table->foreignIdFor(Tenants::class)->constrained()->cascadeOnDelete();
            $table->foreignIdFor(Tiers::class)->constrained()->cascadeOnDelete();
            $table->foreignIdFor(Project::class)->nullable()->constrained()->nullOnDelete();
            $table->foreignIdFor(Quote::class)->nullable()->constrained()->nullOnDelete();

            $table->string('type')->default(\App\Enums\Commerce\InvoiceType::Normal->value);
            $table->string('reference');
            $table->integer('situation_number')->nullable();

            $table->decimal('total_ht', 15, 2)->default(0);
            $table->decimal('total_tva', 15, 2)->default(0);
            $table->decimal('total_ttc', 15, 2)->default(0);

            $table->decimal('retenue_garantie_pct', 5, 2)->default(0);
            $table->decimal('retenue_garantie_amount', 15, 2)->default(0);
            $table->date('retenue_garantie_release_date')->nullable();
            $table->boolean('is_retenue_garantie_released')->default(false);
            $table->timestamp('retenue_garantie_released_at')->nullable();
            $table->string('reception_report_path')->nullable();

            $table->decimal('compte_prorata_amount', 15, 2)->default(0);
            $table->boolean('is_autoliquidation')->default(false);

            $table->string('status')->default(\App\Enums\Commerce\InvoiceStatus::Draft->value);
            $table->date('due_date');
            $table->timestamps();
            $table->softDeletes();

            $table->unique(['tenants_id', 'reference']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('invoices');
    }
};
