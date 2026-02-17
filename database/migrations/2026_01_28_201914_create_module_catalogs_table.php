<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('module_catalogs', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('slug');
            $table->text('description')->nullable();
            $table->boolean('is_core')->default(false);
            $table->decimal('price_monthly')->nullable();
            $table->decimal('price_yearly')->nullable();
            $table->integer('sort_order')->default(0);
            $table->boolean('is_active')->default(true);
            $table->timestamps();

            $table->unique(['name']);
            $table->unique(['slug']);
            $table->index(['slug']);
            $table->index(['is_core']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('module_catalogs');
    }
};
