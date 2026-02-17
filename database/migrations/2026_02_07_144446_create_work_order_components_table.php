<?php

use App\Models\Articles\Article;
use App\Models\GPAO\WorkOrder;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('work_order_components', function (Blueprint $table) {
            $table->id();
            $table->foreignIdFor(WorkOrder::class)->constrained()->cascadeOnDelete();
            $table->foreignIdFor(Article::class)->constrained();
            $table->string('label');
            $table->decimal('quantity_planned', 12, 3);
            $table->decimal('quantity_consumed', 12, 3)->default(0);
            $table->decimal('unit_cost_ht', 12, 2)->default(0);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('work_order_components');
    }
};
