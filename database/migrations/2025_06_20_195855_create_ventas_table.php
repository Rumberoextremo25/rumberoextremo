<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        // Crea la tabla 'ventas'
        Schema::create('ventas', function (Blueprint $table) {
            $table->id(); // Columna para el ID autoincremental (clave primaria)
            $table->decimal('total', 10, 2); // Columna para el monto total de la venta, con 10 dígitos en total y 2 decimales
            $table->dateTime('sale_date')->index(); // Columna para la fecha y hora de la venta, con un índice para búsquedas eficientes
            $table->timestamps(); // Columnas 'created_at' y 'updated_at' automáticas
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        // Elimina la tabla 'ventas' si la migración se revierte
        Schema::dropIfExists('ventas');
    }
};
