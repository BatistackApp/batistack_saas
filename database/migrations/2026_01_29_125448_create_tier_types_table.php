<?php

use App\Models\Tiers\Tiers;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('tier_types', function (Blueprint $table) {
            $table->id();
            $table->foreignIdFor(Tiers::class)->constrained()->cascadeOnDelete();
            $table->string('type')->default(\App\Enums\Tiers\TierType::Customer->value);
            $table->boolean('is_primary')->default(false);
            $table->timestamps();

            $table->unique(['tiers_id', 'type']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('tier_types');
    }
};
