<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::table('time_entries', function (Blueprint $table) {
            // Ajout de la colonne pour le motif de rejet
            $table->text('rejection_note')->nullable()->after('status');
            // On s'assure que les timestamps de validation existent
            $table->timestamp('verified_at')->nullable()->after('verified_by');
            $table->timestamp('approved_at')->nullable()->after('verified_at');

            $table->foreignIdFor(\App\Models\User::class, 'approved_by')->nullable()->constrained()->nullOnDelete();
        });
    }

    public function down(): void
    {
        Schema::table('time_entries', function (Blueprint $table) {
            $table->dropColumn(['rejection_note', 'verified_at', 'approved_at', 'approved_by']);
        });
    }
};
