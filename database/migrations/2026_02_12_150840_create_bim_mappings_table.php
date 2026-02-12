<?php

use App\Models\Bim\BimObject;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('bim_mappings', function (Blueprint $table) {
            $table->id();
            $table->foreignIdFor(BimObject::class)->constrained()->cascadeOnDelete();
            $table->morphs('mappable'); // Permet de lier à Article, ProjectPhase, etc.
            $table->string('color_override', 7)->nullable()->comment('Code HEX pour le rendu 3D');
            $table->json('metadata')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('bim_mappings');
    }
};
