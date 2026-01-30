<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('tier_document_requirements', function (Blueprint $table) {
            $table->id();
            $table->string('tier_type')->default(\App\Enums\Tiers\TierType::Supplier->value);
            $table->string('document_type'); // ex: URSSAF, DECENNALE, DC4
            $table->boolean('is_mandatory')->default(true);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('tier_document_requirements');
    }
};
