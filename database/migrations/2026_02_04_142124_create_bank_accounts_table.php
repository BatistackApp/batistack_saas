<?php

use App\Models\Core\Tenants;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('bank_accounts', function (Blueprint $table) {
            $table->id();
            $table->foreignIdFor(Tenants::class)->constrained()->cascadeOnDelete();

            $table->string('name');
            $table->string('bank_name')->nullable();
            $table->string('bank_iban')->nullable();
            $table->string('type')->default(\App\Enums\Banque\BankAccountType::Current->value);

            $table->string('bridge_id')->nullable();
            $table->string('bridge_item_id')->nullable();
            $table->string('sync_status')->default(\App\Enums\Banque\BankSyncStatus::Pending->value);
            $table->dateTime('last_synced_at')->nullable();

            $table->decimal('initial_balance', 15, 2)->default(0);
            $table->decimal('current_balance', 15, 2)->default(0);

            $table->boolean('is_active')->default(true);
            $table->timestamps();
            $table->softDeletes();

            $table->index(['tenants_id', 'bridge_id']);
            $table->index(['tenants_id', 'bridge_item_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('bank_accounts');
    }
};
