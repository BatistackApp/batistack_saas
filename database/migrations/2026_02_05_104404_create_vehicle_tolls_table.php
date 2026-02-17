<?php

use App\Models\Fleet\Vehicle;
use App\Models\Projects\Project;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('vehicle_tolls', function (Blueprint $table) {
            $table->id();
            $table->foreignIdFor(Vehicle::class)->constrained()->cascadeOnDelete();
            $table->foreignIdFor(Project::class)->nullable()->constrained()->nullOnDelete();
            $table->dateTime('entry_at')->nullable();
            $table->dateTime('exit_at')->nullable();
            $table->string('entry_station')->nullable();
            $table->string('exit_station')->nullable();
            $table->decimal('amount_ht', 15, 2);
            $table->string('external_transaction_id')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('vehicle_tolls');
    }
};
