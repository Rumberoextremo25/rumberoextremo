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
        Schema::create('subcategories', function (Blueprint $table) {
            $table->id(); // Crea un campo 'id' autoincremental, clave primaria (BIGINT UNSIGNED)
            $table->string('name'); // Nombre de la subcategoría


            $table->foreignId('category_id')
                  ->constrained('categories') // Referencia a la tabla 'categories'
                  ->onDelete('cascade'); // Elimina subcategorías si se elimina la categoría padre

            $table->timestamps(); // Agrega las columnas 'created_at' y 'updated_at'
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('subcategories');
    }
};