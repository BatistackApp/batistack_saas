<?php

use App\Models\Projects\Project;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('project_amendments', function (Blueprint $table) {
            $table->id();
            $table->foreignIdFor(Project::class)->constrained()->cascadeOnDelete();
            $table->string('reference');
            $table->string('description')->nullable();
            $table->decimal('amount_ht', 15, 2);
            $table->string('status')->default(\App\Enums\Projects\ProjectAmendmentStatus::Pending->value);

            $table->timestamps();
            $table->unique(['reference']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('project_amendments');
    }
};
