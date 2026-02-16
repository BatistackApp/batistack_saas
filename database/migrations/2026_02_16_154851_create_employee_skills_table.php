<?php

use App\Models\HR\Employee;
use App\Models\HR\Skill;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('employee_skills', function (Blueprint $table) {
            $table->id();
            $table->foreignIdFor(Employee::class)->constrained()->cascadeOnDelete();
            $table->foreignIdFor(Skill::class)->constrained()->cascadeOnDelete();

            $table->date('issue_date')->nullable();
            $table->date('expiry_date')->nullable();

            $table->string('reference_number')->nullable()->comment('N° de certificat ou permis');
            $table->string('document_path')->nullable()->comment('Scan du document');
            $table->integer('level')->default(1)->comment('Niveau 1 à 5 pour les hard skills');
            $table->text('notes')->nullable();
            $table->timestamps();
            $table->softDeletes();

            $table->index(['employee_id', 'expiry_date']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('employee_skills');
    }
};
