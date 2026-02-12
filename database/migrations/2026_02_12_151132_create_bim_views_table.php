<?php

use App\Models\Bim\BimModel;
use App\Models\User;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('bim_views', function (Blueprint $table) {
            $table->id();
            $table->foreignIdFor(BimModel::class)->constrained()->cascadeOnDelete();
            $table->foreignIdFor(User::class)->constrained();
            $table->string('name');
            $table->json('camera_state')->comment('Position, Rotation, Target du viewer');
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('bim_views');
    }
};
