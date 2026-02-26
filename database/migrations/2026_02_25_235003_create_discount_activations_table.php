<?php
// database/migrations/[timestamp]_create_discount_activations_table.php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('discount_activations', function (Blueprint $table) {
            $table->id();
            
            // Relaciones con otras tablas
            $table->foreignId('user_id')
                  ->constrained()
                  ->onDelete('cascade')
                  ->comment('Usuario que activa el descuento');
            
            $table->foreignId('ally_id')
                  ->constrained('allies')
                  ->onDelete('cascade')
                  ->comment('Aliado que ofrece el descuento');
            
            $table->foreignId('promotion_id')
                  ->constrained('promotions')
                  ->onDelete('cascade')
                  ->comment('Promoción activada');
            
            // Información de la activación
            $table->string('code', 50)
                  ->unique()
                  ->comment('Código único para canjear');
            
            $table->string('discount', 50)
                  ->comment('Descuento aplicado (ej: 20%, 2x1)');
            
            $table->string('title')
                  ->comment('Título de la promoción');
            
            $table->text('description')
                  ->nullable()
                  ->comment('Descripción al momento de activar');
            
            // Estado y control
            $table->enum('status', [
                'active',    // Activo y disponible
                'used',      // Ya fue canjeado
                'expired',   // Venció sin usar
                'cancelled'  // Cancelado por el usuario/sistema
            ])->default('active')->comment('Estado actual');
            
            $table->timestamp('expires_at')
                  ->comment('Fecha de expiración del código');
            
            $table->timestamp('used_at')
                  ->nullable()
                  ->comment('Fecha de canje');
            
            $table->timestamp('cancelled_at')
                  ->nullable()
                  ->comment('Fecha de cancelación');
            
            // Información adicional
            $table->json('metadata')
                  ->nullable()
                  ->comment('Datos adicionales (ubicación, términos aceptados, etc)');
            
            $table->string('ip_address', 45)
                  ->nullable()
                  ->comment('IP desde donde se activó');
            
            $table->string('device_info')
                  ->nullable()
                  ->comment('Información del dispositivo');
            
            $table->timestamps(); // created_at, updated_at
            
            // Índices para búsquedas rápidas
            $table->index(['user_id', 'status']);
            $table->index(['ally_id', 'status']);
            $table->index(['promotion_id', 'status']);
            $table->index('code');
            $table->index('expires_at');
            $table->index('status');
            
            // Índice compuesto para consultas comunes
            $table->index(['user_id', 'status', 'expires_at']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('discount_activations');
    }
};
