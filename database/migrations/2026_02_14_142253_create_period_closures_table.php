<?php

use App\Models\Core\Tenants;
use App\Models\User;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('period_closures', function (Blueprint $table) {
            $table->id();
            $table->ulid('ulid')->unique();
            $table->foreignIdFor(Tenants::class)->constrained()->cascadeOnDelete();
            $table->integer('month');
            $table->integer('year');
            $table->date('period_start');
            $table->date('period_end');
            $table->boolean('is_locked')->default(false);
            $table->foreignIdFor(User::class, 'closed_by')->nullable()->constrained()->nullOnDelete();
            $table->dateTime('closed_at')->nullable();
            $table->timestamps();
            $table->softDeletes();

            $table->unique(['tenants_id', 'month', 'year']);
            $table->index(['tenants_id', 'is_locked']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('period_closures');
    }
};
