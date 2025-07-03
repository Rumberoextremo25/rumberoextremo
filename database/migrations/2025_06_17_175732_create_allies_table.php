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
        Schema::create('allies', function (Blueprint $table) {
            $table->id(); // ID único para el registro de aliado

            // --- Clave Foránea para la Relación Uno a Uno con 'users' ---
            // Esto es FUNDAMENTAL para que este perfil de aliado pertenezca a un usuario
            $table->foreignId('user_id')
                ->unique() // Un usuario solo puede tener un perfil de aliado
                ->constrained('users') // Hace referencia a la tabla 'users'
                ->onDelete('cascade'); // Si se elimina el usuario, se elimina su perfil de aliado

            // --- Campos de Información Específica del Aliado ---
            $table->string('company_name')->nullable(); // Nombre oficial de la empresa del aliado (antes 'name')
            $table->string('company_rif', 50)->nullable(); // RIF de la empresa (antes no existía)
            $table->string('service_category')->nullable(); // Categoría de servicio (antes 'type')
            $table->string('website_url')->nullable(); // URL del sitio web (antes no existía)
            $table->decimal('discount', 5, 2)->nullable(); // Porcentaje de descuento ofrecido (antes no existía)

            // --- Campos de Contacto del Aliado (Si son distintos al contacto del User principal) ---
            // Estos campos son útiles si el contacto de la empresa es diferente al contacto del usuario vinculado
            $table->string('contact_person_name')->nullable(); // Nombre de la persona de contacto en la empresa
            $table->string('contact_email')->nullable(); // Email de contacto de la empresa
            $table->string('contact_phone', 20)->nullable(); // Teléfono de contacto de la empresa (added length for consistency)
            $table->string('contact_phone_alt', 20)->nullable(); // **CAMPO FALTANTE: Teléfono de contacto adicional**
            $table->text('company_address')->nullable(); // Dirección de la empresa

            // --- Otros Campos ---
            $table->text('notes')->nullable(); // **CAMPO FALTANTE: Notas internas**

            $table->timestamp('registered_at')->nullable(); // Fecha en que se registró como aliado
            $table->string('status')->default('activo'); // Estado del aliado (activo, inactivo, pendiente)

            $table->timestamps(); // created_at y updated_at para el registro del aliado
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('allies');
    }
};
