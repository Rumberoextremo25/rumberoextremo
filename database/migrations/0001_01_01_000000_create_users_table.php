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
        Schema::create('users', function (Blueprint $table) {
            $table->id();
            $table->string('name')->nullable();
            $table->string('firstname')->nullable(); // Tus nombres de columna actuales
            $table->string('lastname')->nullable();  // Tus nombres de columna actuales
            $table->string('email')->unique();
            $table->timestamp('email_verified_at')->nullable();
            $table->string('password');            

            // --- Campos de Two-Factor Authentication ---
            $table->text('two_factor_secret')->nullable();
            $table->text('two_factor_recovery_codes')->nullable();
            $table->boolean('two_factor_enabled')->default(false);

            // --- Campos de Preferencias de Usuario ---
            $table->boolean('notifications_enabled')->default(true);
            $table->boolean('dark_mode_enabled')->default(false);

            // --- Información del Perfil ---
            $table->string('role')->default('comun');
            $table->string('user_type')->default('user')->comment('Indica si el usuario es "user" (rumbero) o "partner" (aliado)');
            $table->string('identification', 50)->nullable()->unique();
            $table->string('full_name')->nullable();
            $table->date('dob')->nullable();
            $table->string('phone1', 20)->nullable();
            $table->string('phone2', 20)->nullable();
            $table->text('address')->nullable();
            $table->string('profile_photo_path', 2048)->nullable();
            $table->string('profile_photo_url', 2048)->nullable();
            $table->timestamp('last_login_at')->nullable();

            // --- CAMPOS QUE GENERARON EL ERROR (¡AHORA SIN 'after'!) ---
            $table->string('status')->default('activo'); // NO 'after phone2'
            $table->date('registration_date')->nullable(); // NO 'after status'
            $table->text('notes')->nullable(); // NO 'after registration_date'

            // --- Campos para Usuarios "Aliados" ---
            $table->boolean('is_ally')->default(false);
            $table->string('allied_company_name')->nullable();
            $table->string('allied_company_rif', 50)->nullable();
            $table->string('service_category')->nullable();
            $table->string('website_url')->nullable();
            $table->decimal('discount', 5, 2)->nullable();
            $table->timestamp('allied_registered_at')->nullable();

            // --- Campos por Defecto de Laravel ---
            $table->rememberToken();
            $table->timestamps(); // created_at y updated_at
        });

        // Tus otras tablas están bien si no tienen el mismo problema
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