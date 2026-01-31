<?php

use App\Models\Articles\Article;
use App\Models\Articles\Warehouse;
use App\Models\Core\Tenants;
use App\Models\Projects\Project;
use App\Models\Projects\ProjectPhase;
use App\Models\User;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('stock_movements', function (Blueprint $table) {
            $table->id();
            $table->foreignIdFor(Tenants::class)->constrained()->cascadeOnDelete();
            $table->foreignIdFor(Article::class)->constrained()->cascadeOnDelete();
            $table->foreignIdFor(Warehouse::class)->constrained()->cascadeOnDelete();

            $table->foreignIdFor(Project::class)->nullable()->constrained()->nullOnDelete();
            $table->foreignIdFor(ProjectPhase::class)->nullable()->constrained()->nullOnDelete();
            $table->foreignIdFor(Warehouse::class, 'target_warehouse_id')->nullable()->constrained('warehouses')->nullOnDelete();

            $table->string('type')->default(\App\Enums\Articles\StockMovementType::Adjustment->value);
            $table->string('adjustement_type')->nullable();

            $table->decimal('quantity', 15, 3)->default(0);
            $table->decimal('unit_cost_ht', 15, 2)->nullable();
            $table->string('reference')->nullable();
            $table->text('notes')->nullable();

            $table->foreignIdFor(User::class)->nullable()->constrained()->nullOnDelete();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('stock_movements');
    }
};
