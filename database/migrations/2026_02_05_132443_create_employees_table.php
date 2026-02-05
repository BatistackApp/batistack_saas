<?php

use App\Models\Core\Tenants;
use App\Models\User;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('employees', function (Blueprint $table) {
            $table->id();
            $table->foreignIdFor(Tenants::class)->constrained()->cascadeOnDelete();
            $table->foreignIdFor(User::class)->nullable()->constrained()->nullOnDelete();

            $table->string('external_id')->nullable()->comment('ID logiciel de paie');
            $table->string('first_name');
            $table->string('last_name');
            $table->string('job_title')->nullable();

            $table->decimal('hourly_cost_charged', 15, 2)->default(0);

            $table->date('contract_end_date')->nullable();
            $table->date('hired_at')->nullable();
            $table->boolean('is_active')->default(true);
            $table->timestamps();
            $table->softDeletes();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('employees');
    }
};
