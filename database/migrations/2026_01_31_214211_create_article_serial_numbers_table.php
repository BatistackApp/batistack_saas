<?php

use App\Models\Articles\Article;
use App\Models\Articles\Warehouse;
use App\Models\Projects\Project;
use App\Models\User;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('article_serial_numbers', function (Blueprint $table) {
            $table->id();
            $table->foreignIdFor(\App\Models\Core\Tenants::class)->constrained()->cascadeOnDelete();
            $table->foreignIdFor(Article::class)->constrained()->cascadeOnDelete();
            $table->foreignIdFor(Warehouse::class)->nullable()->constrained()->nullOnDelete();

            $table->string('serial_number')->index();
            $table->string('status')->default(\App\Enums\Articles\SerialNumberStatus::InStock->value);

            $table->foreignIdFor(Project::class)->nullable()->constrained()->nullOnDelete();
            $table->foreignIdFor(User::class, 'assigned_user_id')->nullable()->constrained()->nullOnDelete();

            $table->string('photo_plate_path')->nullable();
            $table->date('purchase_date')->nullable();
            $table->date('warranty_expiry')->nullable();
            $table->timestamps();

            $table->unique(['article_id', 'serial_number', 'tenants_id']);
        });

        Schema::table('stock_movements', function (Blueprint $table) {
            $table->foreignId('serial_number_id')->nullable()->constrained('article_serial_numbers')->nullOnDelete();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('article_serial_numbers');
        Schema::table('stock_movements', function (Blueprint $table) {
            $table->dropForeign('serial_number_id');
        });
    }
};
