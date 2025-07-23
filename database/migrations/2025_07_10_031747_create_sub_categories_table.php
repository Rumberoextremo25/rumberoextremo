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
        Schema::create('sub_categories', function (Blueprint $table) {
            $table->id();
            $table->foreignId('category_id') // Clave foránea que enlaza a la categoría padre
                  ->constrained('categories') // Hace referencia a la tabla 'categories'
                  ->onDelete('cascade'); // Si la categoría padre se elimina, sus subcategorías también

            $table->string('name'); // Nombre de la subcategoría (ej: "Música en Vivo", "Comida Rápida")
            $table->string('slug')->unique(); // Slug único para URLs amigables
            $table->text('description')->nullable(); // Descripción opcional de la subcategoría
            $table->timestamps();

            // Asegura que no haya dos subcategorías con el mismo nombre dentro de la misma categoría
            $table->unique(['category_id', 'name']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('sub_categories');
    }
};