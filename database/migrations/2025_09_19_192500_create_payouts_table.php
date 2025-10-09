<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('payouts', function (Blueprint $table) {
            $table->id();

            // Foreign keys - CORREGIR: Quitar default(0) y usar constrained
            $table->foreignId('sale_id')->nullable()->constrained('sales')->onDelete('cascade');
            $table->foreignId('ally_id')->nullable()->constrained('allies')->onDelete('cascade');

            // Amount fields - Pago al aliado - AGREGAR DEFAULTS
            $table->decimal('sale_amount', 12, 2)->default(0);
            $table->decimal('commission_percentage', 5, 2)->default(0);
            $table->decimal('commission_amount', 12, 2)->default(0);
            $table->decimal('net_amount', 12, 2)->default(0);

            // Campos para transferencia a la empresa - AGREGAR DEFAULTS
            $table->decimal('company_transfer_amount', 12, 2)->default(0);
            $table->decimal('company_commission', 12, 2)->default(0);
            $table->string('company_account', 50)->nullable()->default(null);
            $table->string('company_bank', 50)->nullable()->default(null);
            $table->string('company_transfer_reference', 100)->nullable()->default(null);
            $table->enum('company_transfer_status', ['pending', 'completed', 'failed'])->default('completed');

            // Status and dates - AGREGAR DEFAULTS
            $table->string('status', 20)->default('pending');
            $table->timestamp('generation_date')->useCurrent(); // Fecha actual por defecto
            $table->timestamp('payment_date')->nullable()->default(null);
            $table->timestamp('company_transfer_date')->nullable()->default(null);

            // References
            $table->string('sale_reference', 100)->nullable()->default(null);
            $table->string('payment_reference', 100)->nullable()->default(null);

            // Payment details - Aliado
            $table->string('ally_payment_method', 50)->default('transfer');
            $table->string('ally_account_number', 50)->nullable()->default(null);
            $table->string('ally_bank', 50)->nullable()->default(null);

            // Proof and notes
            $table->string('payment_proof')->nullable()->default(null);
            $table->string('company_transfer_proof')->nullable()->default(null);
            $table->text('notes')->nullable()->default(null);
            $table->text('company_transfer_notes')->nullable()->default(null);

            // Response data
            $table->json('company_transfer_response')->nullable()->default(null);

            // Timestamps
            $table->timestamps();

            // Indexes
            $table->index('sale_id');
            $table->index('ally_id');
            $table->index('status');
            $table->index('company_transfer_status');
            $table->index('generation_date');
            $table->index('payment_date');
            $table->index('company_transfer_date');
            $table->index('sale_reference');
            $table->index('company_transfer_reference');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('payouts');
    }
};
