<?php

use App\Models\Projects\ProjectPhase;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::table('expense_items', function (Blueprint $table) {
            $table->foreignIdFor(ProjectPhase::class)
                ->nullable()
                ->after('project_id')
                ->constrained()
                ->nullOnDelete();
        });
    }

    public function down(): void
    {
        Schema::table('expense_items', function (Blueprint $table) {
            $table->dropConstrainedForeignIdFor(ProjectPhase::class);
        });
    }
};
