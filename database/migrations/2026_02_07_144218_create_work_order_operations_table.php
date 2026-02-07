<?php

use App\Models\GPAO\WorkCenter;
use App\Models\GPAO\WorkOrder;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('work_order_operations', function (Blueprint $table) {
            $table->id();
            $table->foreignIdFor(WorkOrder::class)->constrained()->cascadeOnDelete();
            $table->foreignIdFor(WorkCenter::class)->constrained();
            $table->integer('sequence')->default(10)->comment("définit l'ordre chronologique et logique des étapes de fabrication (la gamme).");
            $table->string('label');
            $table->decimal('time_planned_minutes', 10, 2)->default(0);
            $table->decimal('time_actual_minutes', 10, 2)->default(0);
            $table->string('status')->default(\App\Enums\GPAO\OperationStatus::Pending->value);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('work_order_operations');
    }
};
