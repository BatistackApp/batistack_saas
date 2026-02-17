<?php

use App\Models\Articles\Article;
use App\Models\Locations\RentalContract;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('rental_items', function (Blueprint $table) {
            $table->id();
            $table->foreignIdFor(RentalContract::class)->constrained()->cascadeOnDelete();
            $table->foreignIdFor(Article::class)->nullable()->constrained();
            $table->string('label');
            $table->decimal('quantity', 10, 2)->default(1);

            $table->decimal('daily_rate_ht', 12, 2)->default(0);
            $table->decimal('weekly_rate_ht', 12, 2)->default(0);
            $table->decimal('monthly_rate_ht', 12, 2)->default(0);

            $table->boolean('is_weekend_included')->default(false);
            $table->float('insurance_pct')->default(0)->comment('% assurance bris de machine');

            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('rental_items');
    }
};
