<?php

use App\Models\Core\Tenant;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('payroll_exports', function (Blueprint $table) {
            $table->id();
            $table->uuid('uuid')->unique();
            $table->foreignIdFor(Tenant::class)->constrained()->cascadeOnDelete();
            $table->string('format')->default(\Filament\Actions\Exports\Enums\ExportFormat::Csv->value);
            $table->year('year');
            $table->unsignedTinyInteger('month');
            $table->string('file_path');
            $table->string('file_name');
            $table->bigInteger('file_size')->default(0);
            $table->integer('payroll_count')->default(0);
            $table->text('notes')->nullable();
            $table->timestamp('exported_at');
            $table->timestamps();
            $table->softDeletes();

            $table->index(['tenant_id', 'year', 'month']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('payroll_exports');
    }
};
