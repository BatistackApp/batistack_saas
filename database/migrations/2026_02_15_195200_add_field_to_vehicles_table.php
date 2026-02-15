<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::table('vehicles', function (Blueprint $table) {
            $table->integer('current_hours')->default(0)->after('current_odometer');
            $table->boolean('is_available')->default(true)->after('current_hours');
        });
    }

    public function down(): void
    {
        Schema::table('vehicles', function (Blueprint $table) {
            $table->dropColumn('current_hours');
            $table->dropColumn('is_available');
        });
    }
};
