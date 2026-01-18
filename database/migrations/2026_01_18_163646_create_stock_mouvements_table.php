<?php

use App\Models\Articles\Article;
use App\Models\Articles\Warehouse;
use App\Models\Core\Tenant;
use App\Models\User;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('stock_mouvements', function (Blueprint $table) {
            $table->id();
            $table->foreignIdFor(Tenant::class)->constrained()->cascadeOnDelete();
            $table->foreignIdFor(Article::class)->constrained()->cascadeOnDelete();
            $table->foreignIdFor(Warehouse::class)->constrained()->cascadeOnDelete();
            $table->string('type')->default(\App\Enums\Articles\StockMouvementType::Entree->value);
            $table->string('reason')->default(\App\Enums\Articles\StockMouvementReason::AjustementManuel->value);
            $table->decimal('quantity', 12, 3);
            $table->string('reference')->nullable();
            $table->text('notes')->nullable();
            $table->foreignIdFor(User::class, 'created_by')->constrained()->cascadeOnDelete();
            $table->timestamp('mouvement_date')->useCurrent();
            $table->timestamps();
            $table->softDeletes();

            $table->index(['tenant_id', 'article_id', 'warehouse_id']);
            $table->index(['mouvement_date']);

        });
    }

    public function down(): void
    {
        Schema::dropIfExists('stock_mouvements');
    }
};
