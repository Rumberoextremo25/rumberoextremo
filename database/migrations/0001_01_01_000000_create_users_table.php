<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

use function Laravel\Prompts\table;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('users', function (Blueprint $table) {
            $table->id();

            // --- Campos de Autenticación Básicos ---
            $table->string('name')->nullable(); // Campo 'name' general
            $table->string('email')->unique();
            $table->timestamp('email_verified_at')->nullable();
            $table->string('password');
            $table->integer('age')->nullable();
            $table->string('user_type')->default('user')->comment('Indica si el usuario es "user" (rumbero) o "partner" (aliado)');
            // --- Información de Perfil General ---
            $table->string('firstname')->nullable()->comment('Primer nombre del usuario');
            $table->string('lastname')->nullable()->comment('Apellido del usuario');
            $table->string('identification', 50)->nullable()->unique()->comment('Número de identificación (cédula/pasaporte)');
            $table->date('dob')->nullable()->comment('Fecha de nacimiento');
            $table->string('phone1', 20)->nullable()->comment('Número de teléfono principal');
            $table->string('phone2', 20)->nullable()->comment('Número de teléfono secundario');
            $table->text('address')->nullable()->comment('Dirección completa del usuario');
            $table->string('profile_photo_path', 2048)->nullable()->comment('Ruta al archivo de la foto de perfil');
            $table->string('profile_photo_url', 2048)->nullable()->comment('URL de la foto de perfil (si se usa CDN, etc.)');
            $table->timestamp('last_login_at')->nullable()->comment('Fecha y hora del último inicio de sesión');
            $table->string('status')->default('activo')->comment('Estado del usuario (activo, inactivo, suspendido)');
            $table->date('registration_date')->nullable()->comment('Fecha de registro del usuario');
            $table->text('notes')->nullable()->comment('Notas internas sobre el usuario');

            // --- Campos de Roles y Permisos ---
            $table->string('role')->default('comun')->comment('Rol del usuario en el sistema (ej. admin, comun)');

            // --- Campos de Two-Factor Authentication (Laravel Jetstream/Fortify) ---
            $table->text('two_factor_secret')->nullable();
            $table->text('two_factor_recovery_codes')->nullable();
            $table->boolean('two_factor_enabled')->default(false);

            // --- Campos Específicos para Usuarios "Aliados" ---
            // Considera si 'is_ally' es necesario si 'user_type' ya lo define.
            $table->boolean('is_ally')->default(false)->comment('Indica si este usuario es un aliado (redundante si user_type es suficiente)');
            $table->string('allied_company_name')->nullable()->comment('Nombre de la empresa aliada');
            $table->string('allied_company_rif', 50)->nullable()->comment('RIF de la empresa aliada');
            $table->string('service_category')->nullable()->comment('Categoría de servicio que ofrece el aliado');
            $table->string('website_url')->nullable()->comment('URL del sitio web del aliado');
            $table->decimal('discount', 5, 2)->nullable()->comment('Descuento ofrecido por el aliado (ej. 5.25)');
            $table->timestamp('allied_registered_at')->nullable()->comment('Fecha de registro como aliado');

            // --- Campos por Defecto de Laravel ---
            $table->rememberToken();
            $table->timestamps(); // created_at y updated_at
        });

        // Tus otras tablas (password_reset_tokens, sessions) no necesitan cambios si ya están correctas.
        Schema::create('password_reset_tokens', function (Blueprint $table) {
            $table->string('email')->primary();
            $table->string('token');
            $table->timestamp('created_at')->nullable();
        });

        Schema::create('sessions', function (Blueprint $table) {
            $table->string('id')->primary();
            $table->foreignId('user_id')->nullable()->index();
            $table->string('ip_address', 45)->nullable();
            $table->text('user_agent')->nullable();
            $table->longText('payload');
            $table->integer('last_activity')->index();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('users');
        Schema::dropIfExists('password_reset_tokens');
        Schema::dropIfExists('sessions');
    }
};
