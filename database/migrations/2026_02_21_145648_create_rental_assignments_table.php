<?php

use App\Models\Core\Tenants;
use App\Models\Locations\RentalContract;
use App\Models\Projects\Project;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('rental_assignments', function (Blueprint $table) {
            $table->id();
            $table->foreignIdFor(Tenants::class)->constrained()->cascadeOnDelete();
            $table->foreignIdFor(RentalContract::class)->constrained()->cascadeOnDelete();
            $table->foreignIdFor(Project::class)->constrained();
            $table->timestamp('assigned_at');
            $table->timestamp('released_at')->nullable();
            $table->text('notes')->nullable();
            $table->timestamps();

            $table->index(['rental_contract_id', 'project_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('rental_assignments');
    }
};
