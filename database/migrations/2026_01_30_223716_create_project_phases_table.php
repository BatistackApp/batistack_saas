<?php

use App\Models\Projects\Project;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('project_phases', function (Blueprint $table) {
            $table->id();
            $table->foreignIdFor(Project::class)->constrained()->cascadeOnDelete();
            $table->foreignId('depends_on_phase_id')->nullable()->constrained('project_phases')->nullOnDelete();
            $table->string('name');
            $table->decimal('allocated_budget', 15, 2)->default(0);
            $table->integer('order')->default(0);
            $table->string('status')->default(\App\Enums\Projects\ProjectPhaseStatus::Pending->value);
            $table->decimal('progress_percentage', 5, 2)->default(0);
            $table->decimal('rad_labor', 15, 2)->default(0);
            $table->decimal('rad_materials', 15, 2)->default(0);
            $table->decimal('rad_subcontracting', 15, 2)->default(0);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('project_phases');
    }
};
