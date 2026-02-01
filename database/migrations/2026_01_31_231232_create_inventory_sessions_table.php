<?php

use App\Models\Articles\Warehouse;
use App\Models\Core\Tenants;
use App\Models\User;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('inventory_sessions', function (Blueprint $table) {
            $table->id();
            $table->foreignIdFor(Tenants::class)->constrained()->cascadeOnDelete();
            $table->foreignIdFor(Warehouse::class)->constrained()->cascadeOnDelete();

            $table->string('reference')->unique()->comment("Ex: INV-2026-001");
            $table->string('status')->default(\App\Enums\Articles\InventorySessionStatus::Open->value);

            $table->dateTime('opened_at');
            $table->dateTime('closed_at')->nullable();
            $table->dateTime('validated_at')->nullable();

            $table->foreignIdFor(User::class, 'created_by')->constrained()->cascadeOnDelete();
            $table->foreignIdFor(User::class, 'validated_by')->nullable()->constrained()->cascadeOnDelete();

            $table->text('notes')->nullable();
            $table->timestamps();
            $table->softDeletes();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('inventory_sessions');
    }
};
