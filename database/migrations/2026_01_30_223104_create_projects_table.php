<?php

use App\Models\Core\Tenants;
use App\Models\Tiers\Tiers;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('projects', function (Blueprint $table) {
            $table->id();
            $table->foreignIdFor(Tenants::class)->constrained()->cascadeOnDelete();
            $table->foreignIdFor(Tiers::class, 'customer_id')->constrained()->restrictOnDelete();

            $table->string('code_project');
            $table->string('name');
            $table->text('description')->nullable();

            $table->string('address')->nullable();
            $table->decimal('latitude', 10, 8)->nullable();
            $table->decimal('longitude', 10, 8)->nullable();

            $table->decimal('initial_budget_ht', 15, 2)->default(0);
            $table->string('status')->default(\App\Enums\Projects\ProjectStatus::Study->value);

            $table->date('planned_start_at')->nullable();
            $table->date('planned_end_at')->nullable();
            $table->date('actual_start_at')->nullable();
            $table->date('actual_end_at')->nullable();

            $table->timestamps();
            $table->softDeletes();

            $table->unique(['code_project']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('projects');
    }
};
