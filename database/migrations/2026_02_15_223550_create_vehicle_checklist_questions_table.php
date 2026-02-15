<?php

use App\Models\Fleet\VehicleChecklistTemplate;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('vehicle_checklist_questions', function (Blueprint $table) {
            $table->id();
            $table->foreignIdFor(VehicleChecklistTemplate::class, 'template_id')->constrained()->cascadeOnDelete();
            $table->string('label');
            $table->string('description')->nullable();
            $table->string('response_type')->default('boolean')->comment("boolean, text, numeric");
            $table->boolean('is_mandatory')->default(true);
            $table->boolean('requires_photo_on_anomaly')->default(false);
            $table->integer('sort_order')->default(0);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('vehicle_checklist_questions');
    }
};
