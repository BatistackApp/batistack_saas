<?php

use App\Models\Core\Tenants;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('kpi_indicators', function (Blueprint $table) {
            $table->id();
            $table->ulid('ulid')->unique();
            $table->foreignIdFor(Tenants::class)->constrained()->cascadeOnDelete();
            $table->string('code');
            $table->string('name');
            $table->text('description')->nullable();
            $table->string('category');
            $table->string('unit');
            $table->string('formula_class')->nullable()->comment('Classe PHP gérant le calcul');
            $table->boolean('is_active')->default(true);
            $table->timestamps();
            $table->softDeletes();

            $table->index(['tenants_id', 'code']);
            $table->unique(['tenants_id', 'code']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('kpi_indicators');
    }
};
