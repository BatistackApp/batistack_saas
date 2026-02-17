<?php

use App\Models\Articles\Warehouse;
use App\Models\Core\Tenants;
use App\Models\Projects\Project;
use App\Models\Projects\ProjectPhase;
use App\Models\Tiers\Tiers;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('interventions', function (Blueprint $table) {
            $table->id();
            $table->foreignIdFor(Tenants::class)->constrained()->cascadeOnDelete();
            $table->foreignIdFor(Tiers::class, 'customer_id')->constrained();
            $table->foreignIdFor(Warehouse::class)->nullable()->constrained();
            $table->foreignIdFor(Project::class)->nullable()->constrained();
            $table->foreignIdFor(ProjectPhase::class)->nullable()->constrained();

            $table->string('reference');
            $table->string('label');
            $table->text('description')->nullable();
            $table->timestamp('planned_at')->nullable();

            $table->string('status')->default(\App\Enums\Intervention\InterventionStatus::Planned->value);
            $table->string('billing_type')->default(\App\Enums\Intervention\BillingType::Regie->value);

            $table->decimal('amount_ht', 15, 2)->default(0);
            $table->decimal('amount_cost_ht', 15, 2)->default(0);
            $table->decimal('margin_ht', 15, 2)->default(0);
            $table->timestamps();
            $table->softDeletes();

            $table->unique(['tenants_id', 'reference']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('interventions');
    }
};
