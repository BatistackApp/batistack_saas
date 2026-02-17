<?php

use App\Models\Core\Tenants;
use App\Models\GED\DocumentFolder;
use App\Models\User;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('documents', function (Blueprint $table) {
            $table->id();
            $table->foreignIdFor(Tenants::class)->constrained()->cascadeOnDelete();
            $table->foreignIdFor(User::class)->nullable()->constrained()->nullOnDelete();
            $table->foreignIdFor(DocumentFolder::class, 'folder_id')->nullable()->constrained()->nullOnDelete();

            $table->nullableMorphs('documentable');
            $table->string('name');
            $table->string('file_path');
            $table->string('file_name');
            $table->string('extension', 10);
            $table->string('mime_type');
            $table->bigInteger('size');
            $table->json('metadata')->nullable();
            $table->integer('version')->default(1);
            $table->date('expires_at')->nullable();
            $table->timestamps();
            $table->softDeletes();

            $table->index(['tenants_id', 'size']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('documents');
    }
};
