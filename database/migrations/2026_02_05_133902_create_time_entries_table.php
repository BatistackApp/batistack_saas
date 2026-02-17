<?php

use App\Models\Core\Tenants;
use App\Models\HR\Employee;
use App\Models\Projects\Project;
use App\Models\Projects\ProjectPhase;
use App\Models\User;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('time_entries', function (Blueprint $table) {
            $table->id();
            $table->foreignIdFor(Tenants::class)->constrained()->cascadeOnDelete();
            $table->foreignIdFor(Employee::class)->constrained()->cascadeOnDelete();
            $table->foreignIdFor(Project::class)->constrained();
            $table->foreignIdFor(ProjectPhase::class)->nullable()->constrained();

            $table->date('date');
            $table->decimal('hours', 5, 2);
            $table->string('status')->default(\App\Enums\HR\TimeEntryStatus::Draft->value);

            $table->boolean('has_meal_allowance')->default(false);
            $table->boolean('has_host_allowance')->default(false);
            $table->decimal('travel_time', 5, 2)->default(0);

            $table->text('notes')->nullable();
            $table->foreignIdFor(User::class, 'verified_by')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('time_entries');
    }
};
