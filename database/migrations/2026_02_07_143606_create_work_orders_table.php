<?php

use App\Models\Articles\Ouvrage;
use App\Models\Articles\Warehouse;
use App\Models\Core\Tenants;
use App\Models\Projects\Project;
use App\Models\Projects\ProjectPhase;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('work_orders', function (Blueprint $table) {
            $table->id();
            $table->foreignIdFor(Tenants::class)->constrained()->cascadeOnDelete();
            $table->foreignIdFor(Ouvrage::class)->constrained();
            $table->foreignIdFor(Warehouse::class)->constrained(); // Où stocker le produit fini
            $table->foreignIdFor(Project::class)->nullable()->constrained();
            $table->foreignIdFor(ProjectPhase::class)->nullable()->constrained();

            $table->string('reference');
            $table->decimal('quantity_planned', 12, 3);
            $table->decimal('quantity_produced', 12, 3)->default(0);

            $table->string('status')->default(\App\Enums\GPAO\WorkOrderStatus::Draft->value);
            $table->integer('priority')->default(1)->comment("1: Normal, 2: Urgent");

            $table->timestamp('planned_start_at')->nullable();
            $table->timestamp('planned_end_at')->nullable();
            $table->timestamp('actual_start_at')->nullable();
            $table->timestamp('actual_end_at')->nullable();

            $table->decimal('total_cost_ht', 15, 2)->default(0);
            $table->timestamps();
            $table->softDeletes();

            $table->unique(['tenants_id', 'reference']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('work_orders');
    }
};
