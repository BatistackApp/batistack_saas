<?php

use App\Models\Core\Tenants;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('vehicles', function (Blueprint $table) {
            $table->id();
            $table->foreignIdFor(Tenants::class)->constrained()->cascadeOnDelete();

            $table->string('name');
            $table->string('internal_code');
            $table->string('type');
            $table->string('license_plate')->nullable();

            $table->string('brand')->nullable();
            $table->string('model')->nullable();
            $table->string('vin')->nullable();

            $table->string('fuel_type')->default(\App\Enums\Fleet\FuelType::Diesel->value);

            $table->string('external_fuel_card_id')->nullable()->index();
            $table->string('external_toll_tag_id')->nullable()->index();

            $table->decimal('hourly_rate', 10, 2)->default(0);
            $table->decimal('km_rate', 10, 2)->default(0);

            $table->decimal('current_odometer', 15, 2)->default(0);
            $table->string('odometer_unit')->default('km');

            $table->date('purchase_date')->nullable();
            $table->decimal('purchase_price', 15, 2)->nullable();

            $table->dateTime('last_external_sync_at')->nullable();

            $table->boolean('is_active')->default(true);

            $table->timestamps();
            $table->softDeletes();

            $table->unique(['tenants_id', 'internal_code']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('vehicles');
    }
};
