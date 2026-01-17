<?php

use App\Models\Tiers\Tiers;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('tier_addresses', function (Blueprint $table) {
            $table->id();
            $table->string('type');
            $table->string('name')->nullable();
            $table->string('street_address');
            $table->string('postal_code');
            $table->string('city');
            $table->string('country')->default('FR');
            $table->text('additional_info')->nullable();
            $table->boolean('is_default')->default(false);
            $table->foreignIdFor(Tiers::class)->constrained()->cascadeOnDelete();
            $table->timestamps();

            $table->index('tiers_id');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('tier_addresses');
    }
};
