<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Ejecuta las migraciones.
     */
    public function up(): void
    {
        Schema::create('allies', function (Blueprint $table) {
            $table->id(); // ID único para el registro del aliado

            // --- Clave Foránea para la Relación Uno a Uno con 'users' ---
            // Esto es FUNDAMENTAL para que este perfil de aliado pertenezca a un usuario
            $table->foreignId('user_id')
                ->unique() // Un usuario solo puede tener un perfil de aliado
                ->constrained('users') // Hace referencia a la tabla 'users'
                ->onDelete('cascade'); // Si se elimina el usuario, se elimina su perfil de aliado

            // --- Campos de Información Específica del Aliado ---
            $table->string('company_name')->nullable(); // Nombre oficial de la empresa del aliado
            $table->string('company_rif', 50)->nullable(); // RIF de la empresa

            // --- Nuevas Claves Foráneas para Categorización ---
            // Estas reemplazan el campo 'service_category' para una categorización más granular.
            $table->foreignId('category_id')
                  ->nullable()
                  ->constrained('categories')
                  ->onDelete('set null'); // Si la categoría se elimina, el aliado pierde su categoría asignada

            $table->foreignId('sub_category_id')
                  ->nullable()
                  ->constrained('sub_categories')
                  ->onDelete('set null'); // Si la subcategoría se elimina, el aliado pierde su subcategoría asignada

            $table->foreignId('business_type_id')
                  ->nullable()
                  ->constrained('business_types')
                  ->onDelete('set null'); // Si el tipo de negocio se elimina, el aliado pierde su tipo asignado

            $table->string('website_url')->nullable(); // URL del sitio web

            // El campo 'discount' ahora es un string para permitir descripciones como "15% en alquiler" o "2x1"
            $table->string('discount')->nullable(); // Oferta de descuento o beneficio para Rumbero Extremo

            // --- Campos de Contacto del Aliado ---
            // Estos campos son útiles si el contacto de la empresa es diferente al contacto del usuario vinculado
            $table->string('contact_person_name')->nullable(); // Nombre de la persona de contacto principal en la empresa
            $table->string('contact_email')->nullable();       // Email de contacto de la empresa
            $table->string('contact_phone', 20)->nullable();    // Teléfono de contacto principal de la empresa
            $table->string('contact_phone_alt', 20)->nullable(); // Teléfono de contacto adicional
            $table->text('company_address')->nullable();       // Dirección fiscal o de oficina de la empresa

            // --- Otros Campos ---
            $table->text('notes')->nullable(); // Notas internas sobre el aliado

            $table->timestamp('registered_at')->nullable(); // Fecha en que se registró como aliado
            $table->string('status')->default('activo'); // Estado del aliado (activo, inactivo, pendiente)

            $table->timestamps(); // created_at y updated_at para el registro del aliado
        });
    }

    /**
     * Revierte las migraciones.
     */
    public function down(): void
    {
        Schema::dropIfExists('allies');
    }
};