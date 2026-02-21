<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('documents', function (Blueprint $table) {
            $table->string('type')->index();
            $table->string('status')->default(\App\Enums\GED\DocumentStatus::Draft->value);
            $table->boolean('is_valid')->default(false)->after('status');
            $table->foreignId('validated_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamp('validated_at')->nullable();

            $table->index(['tenants_id', 'type']);
            $table->index(['tenants_id', 'status']);
            $table->index(['expires_at', 'status']);
        });
    }

    public function down(): void
    {
        Schema::table('documents', function (Blueprint $table) {
            $table->dropColumn('type');
            $table->dropColumn('status');
            $table->dropColumn('is_valid');
            $table->dropColumn('validated_by');
            $table->dropColumn('validated_at');

            $table->dropIndex(['expires_at', 'status']);

            $table->dropIndex(['tenants_id', 'type']);
            $table->dropIndex(['tenants_id', 'status']);
        });
    }
};
