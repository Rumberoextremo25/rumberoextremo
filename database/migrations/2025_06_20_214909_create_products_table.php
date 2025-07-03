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
        Schema::create('products', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('ally_name'); // Nombre del aliado, puedes cambiarlo a una foreign key a 'allies' si tienes esa tabla
            $table->text('description')->nullable();
            $table->decimal('base_price', 10, 2); // Precio base con 2 decimales
            $table->integer('discount_percentage')->default(0); // Descuento en porcentaje
            $table->decimal('final_price', 10, 2); // Precio final (calculado)
            $table->string('status')->default('Disponible'); // Ej: 'Disponible', 'No Disponible', 'Agotado'
            $table->string('image_path')->nullable();

            $table->foreignId('subcategory_id')
                  ->constrained() // Infiere 'subcategories' tabla
                  ->onDelete('cascade');

            $table->timestamps(); // created_at y updated_at
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('products');
    }
};
