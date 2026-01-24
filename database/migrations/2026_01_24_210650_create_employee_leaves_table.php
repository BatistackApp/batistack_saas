<?php

use App\Models\HR\Employee;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('employee_leaves', function (Blueprint $table) {
            $table->id();
            $table->foreignIdFor(Employee::class)->constrained()->cascadeOnDelete();
            $table->string('leave_type')->default(\App\Enums\HR\LeaveType::Other->value);
            $table->dateTime('start_date');
            $table->dateTime('end_date');
            $table->string('status')->default(\App\Enums\HR\LeaveStatus::Pending->value);
            $table->text('reason')->nullable();
            $table->text('rejection_reason')->nullable();
            $table->timestamps();
            $table->softDeletes();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('employee_leaves');
    }
};
