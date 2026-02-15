<?php

use App\Models\Fleet\VehicleCheck;
use App\Models\Fleet\VehicleChecklistQuestion;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('vehicle_check_results', function (Blueprint $table) {
            $table->id();
            $table->foreignIdFor(VehicleCheck::class)->constrained()->cascadeOnDelete();
            $table->foreignIdFor(VehicleChecklistQuestion::class, 'question_id')->constrained()->cascadeOnDelete();
            $table->string('value')->comment("'ok', 'ko', ou texte libre");
            $table->text('anomaly_description')->nullable();
            $table->string('photo_path')->nullable();
            $table->boolean('is_anomaly')->default(false);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('vehicle_check_results');
    }
};
