<?php

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
        Schema::create('rental_contracts', function (Blueprint $table) {
            $table->id();
            $table->foreignIdFor(Tenants::class)->constrained()->cascadeOnDelete();
            $table->foreignIdFor(Tiers::class, 'provider_id')->constrained();
            $table->foreignIdFor(Project::class)->constrained();
            $table->foreignIdFor(ProjectPhase::class)->nullable()->constrained();

            $table->string('reference');
            $table->string('label');
            $table->date('start_date_planned');
            $table->date('end_date_planned')->nullable();

            $table->timestamp('actual_pickup_at')->nullable();
            $table->timestamp('actual_return_at')->nullable();

            $table->string('status')->default(\App\Enums\Locations\RentalStatus::DRAFT->value);
            $table->text('notes')->nullable();

            $table->timestamps();
            $table->softDeletes();

            $table->unique(['tenants_id', 'reference']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('rental_contracts');
    }
};
