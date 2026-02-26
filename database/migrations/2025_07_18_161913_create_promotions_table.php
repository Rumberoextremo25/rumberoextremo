<?php
// database/migrations/[timestamp]_create_promotions_table.php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('promotions', function (Blueprint $table) {
            $table->id();
            
            // Relación con el aliado (obligatoria)
            $table->foreignId('ally_id')
                  ->constrained('allies')
                  ->onDelete('cascade')
                  ->comment('ID del aliado que ofrece la promoción');
            
            // Información básica de la promoción
            $table->string('title')
                  ->comment('Título de la promoción');
            
            $table->string('image_url')
                  ->nullable()
                  ->comment('URL de la imagen de la promoción');
            
            $table->string('discount')
                  ->comment('Descuento (ej: "20%", "2x1", "€5")');
            
            $table->string('price')
                  ->nullable()
                  ->comment('Precio formateado (ej: "€ 5,00")');
            
            $table->text('description')
                  ->nullable()
                  ->comment('Descripción detallada');
            
            $table->text('terms_conditions')
                  ->nullable()
                  ->comment('Términos y condiciones');
            
            // Control de fechas y estado
            $table->dateTime('expires_at')
                  ->nullable()
                  ->comment('Fecha de expiración');
            
            $table->enum('status', ['active', 'inactive', 'expired'])
                  ->default('active')
                  ->comment('Estado de la promoción');
            
            // Control de usos
            $table->integer('max_uses')
                  ->nullable()
                  ->comment('Máximo de usos permitidos (null = ilimitado)');
            
            $table->integer('current_uses')
                  ->default(0)
                  ->comment('Usos actuales');
            
            // Metadatos adicionales
            $table->json('metadata')
                  ->nullable()
                  ->comment('Datos adicionales en formato JSON');
            
            $table->timestamps();
            
            // Índices para búsquedas eficientes
            $table->index(['ally_id', 'status']);
            $table->index('expires_at');
            $table->index('status');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('promotions');
    }
};
