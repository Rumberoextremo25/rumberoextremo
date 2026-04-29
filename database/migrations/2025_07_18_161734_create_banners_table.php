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
        Schema::create('banners', function (Blueprint $table) {
            $table->id();
            $table->string('title');
            $table->string('image_url'); // Campo para la URL de la imagen
            $table->text('description')->nullable(); // Opcional
            $table->string('target_url')->nullable(); // URL a donde lleva el banner al ser clickeado
            $table->integer('display_order')->default(0); // Para ordenar los banners
            $table->boolean('is_active')->default(true);
            $table->timestamps();
            
            // ✅ Índices para mejorar el rendimiento
            $table->index('display_order');
            $table->index('is_active');
            $table->index(['is_active', 'display_order']); // Índice compuesto para consultas comunes
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('banners');
    }
};
