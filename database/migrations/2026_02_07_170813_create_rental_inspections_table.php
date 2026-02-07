<?php

use App\Models\Locations\RentalContract;
use App\Models\User;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('rental_inspections', function (Blueprint $table) {
            $table->id();
            $table->foreignIdFor(RentalContract::class)->constrained()->cascadeOnDelete();
            $table->foreignIdFor(User::class, 'inspector_id')->constrained();
            $table->string('type');
            $table->text('notes')->nullable();
            $table->json('photos')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('rental_inspections');
    }
};
