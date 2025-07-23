<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('payment_transactions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->nullable()->constrained()->onDelete('set null'); // Si tienes usuarios
            $table->foreignId('ally_id')->constrained()->onDelete('cascade');
            $table->decimal('original_amount', 10, 2);
            $table->decimal('discount_percentage', 5, 2);
            $table->decimal('amount_to_ally', 10, 2);
            $table->decimal('platform_commission', 10, 2);
            $table->string('payment_method'); // 'pago_movil', 'transferencia_bancaria'
            $table->string('status')->default('pending_manual_confirmation'); // pending_manual_confirmation, awaiting_review, confirmed, failed
            $table->string('reference_code')->unique(); // Código que el usuario debe usar
            $table->json('confirmation_data')->nullable(); // Datos de confirmación del usuario
            // $table->string('proof_image_path')->nullable(); // Ruta del comprobante
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('payment_transactions');
    }
};
