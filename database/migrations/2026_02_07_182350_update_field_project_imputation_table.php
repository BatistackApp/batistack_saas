<?php

use App\Models\HR\Employee;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::table('project_imputations', function (Blueprint $table) {
            $table->foreignIdFor(Employee::class)->nullable(true)->change();
        });
    }

    public function down(): void
    {
        Schema::table('project_imputations', function (Blueprint $table) {
            $table->foreignIdFor(Employee::class)->nullable(false)->change();
        });
    }
};
