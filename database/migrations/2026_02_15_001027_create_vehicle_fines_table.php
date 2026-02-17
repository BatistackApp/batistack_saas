<?php

use App\Models\Core\Tenants;
use App\Models\Fleet\FineCategory;
use App\Models\Fleet\Vehicle;
use App\Models\Projects\Project;
use App\Models\User;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('vehicle_fines', function (Blueprint $table) {
            $table->id();
            $table->foreignIdFor(Tenants::class)->constrained();
            $table->foreignIdFor(Vehicle::class)->constrained();
            $table->foreignIdFor(FineCategory::class)->nullable()->constrained();

            $table->foreignIdFor(User::class)->nullable()->constrained();

            $table->string('notice_number')->unique();
            $table->dateTime('offense_at');
            $table->string('location')->nullable();

            $table->decimal('amount_initial', 10, 2);
            $table->decimal('amount_discounted', 10, 2)->nullable();
            $table->decimal('amount_increased', 10, 2)->nullable();
            $table->date('due_date');

            $table->string('status')->default(\App\Enums\Fleet\FinesStatus::Received->value);
            $table->string('designation_status')->default(\App\Enums\Fleet\DesignationStatus::None->value);

            $table->foreignIdFor(Project::class)->nullable()->constrained();
            $table->boolean('is_project_imputable')->default(false);

            $table->string('document_path')->nullable();
            $table->text('notes')->nullable();

            $table->timestamp('exported_at')->nullable();
            $table->string('type')->nullable();
            $table->integer('points_lost')->nullable();

            $table->timestamps();
            $table->softDeletes();

            $table->index(['tenants_id', 'status']);
            $table->index('notice_number');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('vehicle_fines');
    }
};
