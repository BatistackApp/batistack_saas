<?php

use App\Models\HR\Employee;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('employee_timesheets', function (Blueprint $table) {
            $table->id();
            $table->foreignIdFor(Employee::class)->constrained()->cascadeOnDelete();
            $table->date('timesheet_date');
            $table->decimal('total_hours_work', 5, 2)->default(0);
            $table->decimal('total_hours_travel', 5, 2)->nullable();
            $table->string('status')->default(\App\Enums\HR\TimesheetStatus::Draft->value);
            $table->text('notes')->nullable();
            $table->timestamps();

            $table->unique(['employee_id', 'timesheet_date']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('employee_timesheets');
    }
};
