<?php

use App\Models\Core\Tenants;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('journals', function (Blueprint $table) {
            $table->id();
            $table->ulid('ulid')->unique();
            $table->foreignIdFor(Tenants::class)->constrained()->cascadeOnDelete();
            $table->string('code', 3);
            $table->string('label');
            $table->string('type');
            $table->boolean('is_active')->default(true);
            $table->timestamps();
            $table->softDeletes();

            $table->index(['tenants_id', 'code']);
            $table->unique(['tenants_id', 'code']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('journals');
    }
};
