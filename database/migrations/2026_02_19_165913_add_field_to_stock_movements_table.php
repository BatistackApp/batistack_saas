<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::table('stock_movements', function (Blueprint $table) {
            $table->foreignIdFor(\App\Models\Articles\Ouvrage::class)->nullable()->constrained()->nullOnDelete();
        });

        Schema::table('ouvrages', function (Blueprint $table) {
            $table->foreignIdFor(\App\Models\Articles\Article::class)->nullable()->constrained()->nullOnDelete();
            $table->decimal('cump_ht', 15, 2)->nullable();
        });

        Schema::table('time_entries', function (Blueprint $table) {
            $table->decimal('valuation_amount')->default(0);
        });

        Schema::table('projects', function (Blueprint $table) {
            $table->decimal('actual_labor_cost')->default(0);
        });
    }

    public function down(): void
    {
        Schema::table('stock_movements', function (Blueprint $table) {
            $table->dropColumn('ouvrage_id');
            $table->dropForeign('ouvrage_id');
        });

        Schema::table('ouvrages', function (Blueprint $table) {
            $table->dropColumn('articles_id');
            $table->dropForeign('articles_id');
            $table->dropColumn('cump_ht');
        });

        Schema::table('time_entries', function (Blueprint $table) {
            $table->dropColumn('valuation_amount');
        });

        Schema::table('projects', function (Blueprint $table) {
            $table->dropColumn('actual_labor_cost');
        });
    }
};
