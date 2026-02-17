<?php

use App\Models\Accounting\Journal;
use App\Models\Core\Tenants;
use App\Models\User;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('accounting_entries', function (Blueprint $table) {
            $table->id();
            $table->ulid('ulid')->unique();
            $table->foreignIdFor(Tenants::class)->constrained()->cascadeOnDelete();
            $table->foreignIdFor(Journal::class)->constrained()->cascadeOnDelete();
            $table->string('reference_number', 50);
            $table->date('accounting_date');
            $table->string('label');
            $table->text('description')->nullable();
            $table->decimal('total_debit', 15, 4)->default(0);
            $table->decimal('total_credit', 15, 4)->default(0);
            $table->string('status')->default(\App\Enums\Accounting\EntryStatus::Draft->value);
            $table->foreignIdFor(User::class, 'created_by')->nullable()->constrained()->nullOnDelete();
            $table->foreignIdFor(User::class, 'validated_by')->nullable()->constrained()->nullOnDelete();
            $table->dateTime('validated_at')->nullable();
            $table->boolean('created_from_automation')->default(false);
            $table->timestamps();
            $table->softDeletes();

            $table->index(['tenants_id', 'journal_id', 'accounting_date']);
            $table->index(['reference_number']);

            $table->unique(['tenants_id', 'reference_number']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('accounting_entries');
    }
};
