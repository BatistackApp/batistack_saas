<?php

use App\Models\Chantiers\Chantier;
use App\Models\HR\EmployeeTimesheet;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('employee_timesheet_lines', function (Blueprint $table) {
            $table->id();
            $table->foreignIdFor(EmployeeTimesheet::class)->constrained()->cascadeOnDelete();
            $table->foreignIdFor(Chantier::class)->nullable()->constrained()->cascadeOnDelete();
            $table->decimal('hours_work', 5, 2)->nullable();
            $table->decimal('hours_travel', 5, 2)->nullable();
            $table->text('description')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('employee_timesheet_lines');
    }
};
