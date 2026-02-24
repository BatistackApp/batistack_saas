<?php

use App\Models\Core\Tenants;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('payroll_scales', function (Blueprint $table) {
            $table->id();
            $table->foreignIdFor(Tenants::class)->constrained()->cascadeOnDelete();
            $table->string('name')->comment('Ex: Indemnité repas, Cotisation PRO BTP');
            $table->string('slug')->index();
            $table->string('category')->comment('ouvrier, etam, cadre, all');
            $table->decimal('value', 12, 4);
            $table->string('type')->default('fixed');
            $table->date('effective_date')->comment('Date d\'application du barème');
            $table->json('metadata')->nullable()->comment('Pour stocker des paliers si besoin');
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('payroll_scales');
    }
};
