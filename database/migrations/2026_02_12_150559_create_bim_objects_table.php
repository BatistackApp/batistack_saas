<?php

use App\Models\Bim\BimModel;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('bim_objects', function (Blueprint $table) {
            $table->id();
            $table->foreignIdFor(BimModel::class)->constrained()->cascadeOnDelete();
            $table->string('guid')->index()->comment('GUID IFC permanent');
            $table->string('ifc_type')->index()->comment('ex: IfcWall, IfcWindow');
            $table->string('label')->nullable();
            $table->json('properties')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('bim_objects');
    }
};
