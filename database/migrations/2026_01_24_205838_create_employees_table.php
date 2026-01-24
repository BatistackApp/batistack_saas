<?php

use App\Models\Core\Tenant;
use App\Models\User;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('employees', function (Blueprint $table) {
            $table->id();
            $table->foreignIdFor(User::class)->constrained()->cascadeOnDelete();
            $table->foreignIdFor(Tenant::class)->constrained()->cascadeOnDelete();
            $table->string('employee_number');
            $table->string('first_name');
            $table->string('last_name');
            $table->date('hire_date');
            $table->date('resignation_date')->nullable();
            $table->boolean('status')->default(true);
            $table->text('notes')->nullable();
            $table->decimal('hourly_rate', 10, 2)->nullable();
            $table->decimal('transport_allowance', 10, 2)->nullable();
            $table->boolean('has_transport_benefit')->default(false);
            $table->string('iban')->nullable()->comment("IBAN de l'employé");
            $table->timestamps();
            $table->softDeletes();

            $table->unique(['user_id', 'tenant_id']);
            $table->unique(['employee_number', 'tenant_id']);

            $table->index(['hire_date', 'resignation_date']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('employees');
    }
};
