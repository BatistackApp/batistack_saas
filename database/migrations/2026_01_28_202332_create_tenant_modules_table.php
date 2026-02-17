<?php

use App\Models\Core\ModuleCatalog;
use App\Models\Core\Tenants;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('tenant_modules', function (Blueprint $table) {
            $table->id();
            $table->foreignIdFor(Tenants::class)->constrained()->cascadeOnDelete();
            $table->foreignIdFor(ModuleCatalog::class, 'module_id')->constrained()->cascadeOnDelete();
            $table->string('status')->default(\App\Enums\Core\TenantModuleStatus::Active->value);
            $table->dateTime('starts_at');
            $table->dateTime('ends_at')->nullable();
            $table->json('config')->nullable();
            $table->timestamps();

            $table->unique(['tenants_id', 'module_id']);
            $table->index(['tenants_id', 'status']);
        });

        Schema::table('users', function (Blueprint $table) {
            $table->foreignId('tenants_id')->nullable()->constrained()->nullOnDelete();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('tenant_modules');
        Schema::table('users', function (Blueprint $table) {
            $table->removeColumn('tenants_id');
        });
    }
};
