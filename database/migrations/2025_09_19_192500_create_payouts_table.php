<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('payouts', function (Blueprint $table) {
            // Campos de descuento y cálculos
            $table->decimal('ally_discount', 5, 2)->default(0)->after('net_amount');
            $table->decimal('amount_after_discount', 12, 2)->default(0)->after('ally_discount');
            
            // Campos de transferencia a empresa
            $table->decimal('company_transfer_amount', 12, 2)->default(0)->after('amount_after_discount');
            $table->decimal('company_commission', 12, 2)->default(0)->after('company_transfer_amount');
            $table->string('company_account', 50)->nullable()->after('company_commission');
            $table->string('company_bank', 100)->nullable()->after('company_account');
            $table->string('company_transfer_reference', 100)->nullable()->after('company_bank');
            $table->enum('company_transfer_status', ['pending', 'completed', 'failed'])->default('pending')->after('company_transfer_reference');
            $table->timestamp('company_transfer_date')->nullable()->after('company_transfer_status');
            $table->text('company_transfer_response')->nullable()->after('company_transfer_date');
            
            // Campos de estado adicionales
            $table->timestamp('confirmed_at')->nullable()->after('payment_date');
            $table->timestamp('reverted_at')->nullable()->after('confirmed_at');
            
            // Referencias y métodos de pago
            $table->string('payment_reference', 100)->nullable()->after('sale_reference');
            $table->enum('ally_payment_method', ['transfer', 'cash', 'other'])->default('transfer')->after('payment_reference');
            
            // Comprobantes y lotes
            $table->string('payment_proof_path')->nullable()->after('ally_payment_method');
            $table->string('batch_reference', 100)->nullable()->after('payment_proof_path');
            $table->timestamp('batch_processed_at')->nullable()->after('batch_reference');
            
            // Reversión
            $table->text('reversion_reason')->nullable()->after('batch_processed_at');
            
            // Actualizar enum de status
            $table->enum('status', ['pending', 'processing', 'completed', 'reverted', 'failed'])->default('pending')->change();
        });
    }

    public function down(): void
    {
        Schema::table('payouts', function (Blueprint $table) {
            $table->dropColumn([
                'ally_discount',
                'amount_after_discount',
                'company_transfer_amount',
                'company_commission',
                'company_account',
                'company_bank',
                'company_transfer_reference',
                'company_transfer_status',
                'company_transfer_date',
                'company_transfer_response',
                'confirmed_at',
                'reverted_at',
                'payment_reference',
                'ally_payment_method',
                'payment_proof_path',
                'batch_reference',
                'batch_processed_at',
                'reversion_reason'
            ]);
            
            // Revertir enum de status
            $table->enum('status', ['pending', 'completed', 'failed'])->default('pending')->change();
        });
    }
};
