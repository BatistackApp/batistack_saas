<?php

use App\Models\Tiers\Tiers;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('tier_documents', function (Blueprint $table) {
            $table->id();
            $table->foreignIdFor(Tiers::class)->constrained()->cascadeOnDelete();
            $table->string('type'); // ex: ATTESTATION_URSSAF, DECENNALE, KBIS
            $table->string('file_path');
            $table->string('expires_at')->index();
            $table->string('status')->default(\App\Enums\Tiers\TierDocumentStatus::Valid->value); // valid, to_renew, expired
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('tier_documents');
    }
};
