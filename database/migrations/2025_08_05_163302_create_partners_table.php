<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreatePartnersTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('partners', function (Blueprint $table) {
            $table->id(); // Columna autoincremental para la clave primaria
            $table->string('name'); // Nombre o razón social del aliado
            $table->string('email')->unique(); // Correo electrónico del aliado, debe ser único
            $table->string('phone_number')->nullable(); // Número de teléfono (opcional)
            $table->string('address')->nullable(); // Dirección del aliado (opcional)
            $table->string('bank_name')->nullable(); // Nombre del banco del aliado
            $table->string('account_number')->nullable(); // Número de cuenta del aliado
            $table->string('account_type')->nullable(); // Tipo de cuenta (ej: 'corriente', 'ahorro')
            $table->string('id_document')->nullable(); // Cédula de Identidad (CI) o Registro de Información Fiscal (RIF) del aliado
            $table->text('description')->nullable(); // Una breve descripción del aliado
            $table->boolean('is_active')->default(true); // Para activar/desactivar aliados
            $table->timestamps(); // Columnas created_at y updated_at
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('partners');
    }
}