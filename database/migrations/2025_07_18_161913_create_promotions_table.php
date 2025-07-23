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
        Schema::create('promotions', function (Blueprint $table) {
            $table->id();
            $table->string('title');
            $table->string('image_url'); // Campo para la URL de la imagen de la promoción
            $table->string('discount'); // Puede ser un porcentaje o "2x1"
            $table->string('price');    // El precio formateado como string (ej. "€ 5,00")
            $table->dateTime('expires_at')->nullable(); // Fecha de expiración
            $table->text('description')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('promotions');
    }
};
