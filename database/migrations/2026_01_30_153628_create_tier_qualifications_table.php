<?php

use App\Models\Tiers\Tiers;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('tier_qualifications', function (Blueprint $table) {
            $table->id();
            $table->foreignIdFor(Tiers::class)->constrained()->cascadeOnDelete();
            $table->string('label'); // ex: Qualibat 1552, RGE
            $table->string('reference')->nullable();
            $table->date('valid_until')->index();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('tier_qualifications');
    }
};
