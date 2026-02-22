<?php

use App\Models\Core\Tenants;
use App\Models\Tiers\Tiers;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('tier_equipements', function (Blueprint $table) {
            $table->id();
            $table->foreignIdFor(Tenants::class)->constrained()->cascadeOnDelete();
            $table->foreignIdFor(Tiers::class, 'customer_id')->constrained();

            $table->string('name')->comment('Nom usuel (ex: Chaudière Sud)');
            $table->string('brand')->nullable(); // Marque (ex: Viessmann)
            $table->string('model')->nullable(); // Modèle
            $table->string('serial_number')->nullable(); // N° de série
            $table->date('installation_date')->nullable();
            $table->date('warranty_expiration_date')->nullable();

            $table->json('technical_data')->nullable(); // Champs personnalisés (Puissance, gaz, etc.)
            $table->string('location_details')->nullable(); // Précisions sur l'emplacement (ex: Cave, Code accès)
            $table->timestamps();
            $table->softDeletes();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('tier_equipements');
    }
};
