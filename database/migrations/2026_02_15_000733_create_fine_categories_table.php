<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('fine_categories', function (Blueprint $table) {
            $table->id();
            $table->string('label');
            $table->string('antai_code')->nullable(); // Code NATINF
            $table->decimal('default_amount')->nullable(); // Montant par défaut de l'amende
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('fine_categories');
    }
};
