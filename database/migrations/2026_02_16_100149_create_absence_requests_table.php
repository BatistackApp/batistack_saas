<?php

use App\Models\Core\Tenants;
use App\Models\HR\Employee;
use App\Models\User;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('absence_requests', function (Blueprint $table) {
            $table->id();
            $table->foreignIdFor(Tenants::class)->constrained()->cascadeOnDelete();
            $table->foreignIdFor(Employee::class)->constrained()->cascadeOnDelete();
            $table->string('type');
            $table->string('status')->default(\App\Enums\HR\AbsenceRequestStatus::Draft->value);
            $table->dateTime('starts_at');
            $table->dateTime('ends_at');
            $table->decimal('duration_days', 5, 2)->comment('Durée calculée en jours ouvrés');
            $table->text('reason')->nullable();
            $table->string('justification_path')->nullable();
            $table->foreignIdFor(User::class, 'validated_by')->nullable()->constrained()->nullOnDelete();
            $table->dateTime('validated_at')->nullable();
            $table->text('rejection_reason')->nullable();
            $table->timestamps();
            $table->softDeletes();

            $table->index(['employee_id', 'starts_at', 'ends_at']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('absence_requests');
    }
};
