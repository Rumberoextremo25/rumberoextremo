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

            // ==================== FOREIGN KEYS ====================
            $table->foreignId('sale_id')->constrained('sales')->onDelete('cascade');
            $table->foreignId('ally_id')->constrained('allies')->onDelete('cascade');

            // ==================== AMOUNT FIELDS - PAGO AL ALIADO ====================
            $table->decimal('sale_amount', 12, 2)->default(0)->comment('Monto total de la venta');
            $table->decimal('commission_percentage', 5, 2)->default(0)->comment('Porcentaje de comisión');
            $table->decimal('commission_amount', 12, 2)->default(0)->comment('Monto de comisión');
            $table->decimal('net_amount', 12, 2)->default(0)->comment('Monto neto a pagar al aliado');
            $table->decimal('ally_discount', 5, 2)->default(0)->comment('Descuento del aliado en porcentaje');
            $table->decimal('amount_after_discount', 12, 2)->default(0)->comment('Monto después de aplicar descuento del aliado');

            // ==================== CAMPOS DE TRANSFERENCIA A EMPRESA ====================
            $table->decimal('company_transfer_amount', 12, 2)->default(0)->comment('Monto transferido a la empresa');
            $table->decimal('company_commission', 12, 2)->default(0)->comment('Comisión de la empresa');
            $table->string('company_account', 50)->nullable()->comment('Cuenta de la empresa');
            $table->string('company_bank', 100)->nullable()->comment('Banco de la empresa');
            $table->string('company_transfer_reference', 100)->nullable()->comment('Referencia de transferencia a empresa');
            $table->enum('company_transfer_status', ['pending', 'completed', 'failed'])->default('pending')->comment('Estado transferencia empresa');
            $table->timestamp('company_transfer_date')->nullable()->comment('Fecha transferencia empresa');
            $table->text('company_transfer_response')->nullable()->comment('Respuesta JSON de transferencia empresa');

            // ==================== STATUS AND DATES ====================
            $table->enum('status', ['pending', 'processing', 'completed', 'reverted', 'failed'])->default('pending');
            $table->timestamp('generation_date')->useCurrent()->comment('Fecha de generación del payout');
            $table->timestamp('payment_date')->nullable()->comment('Fecha de pago al aliado');
            $table->timestamp('confirmed_at')->nullable()->comment('Fecha de confirmación del pago');
            $table->timestamp('reverted_at')->nullable()->comment('Fecha de reversión');

            // ==================== REFERENCES AND PAYMENT DETAILS ====================
            $table->string('sale_reference', 100)->nullable()->comment('Referencia de la venta');
            $table->string('payment_reference', 100)->nullable()->comment('Referencia del pago al aliado');
            $table->enum('ally_payment_method', ['transfer', 'cash', 'other'])->default('transfer')->comment('Método de pago al aliado');
            
            // ==================== COMPROBANTES Y ARCHIVOS ====================
            $table->string('payment_proof_path')->nullable()->comment('Ruta del comprobante de pago');
            $table->string('batch_reference', 100)->nullable()->comment('Referencia del lote de pago');
            $table->timestamp('batch_processed_at')->nullable()->comment('Fecha de procesamiento del lote');

            // ==================== REVERSION ====================
            $table->text('reversion_reason')->nullable()->comment('Motivo de la reversión');

            // ==================== TIMESTAMPS ====================
            $table->timestamps();

            // ==================== INDEXES ====================
            $table->index('sale_id');
            $table->index('ally_id');
            $table->index('status');
            $table->index('generation_date');
            $table->index('payment_date');
            $table->index('batch_reference');
            $table->index(['status', 'generation_date']);
            $table->index(['ally_id', 'status']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('payouts');
    }
};
