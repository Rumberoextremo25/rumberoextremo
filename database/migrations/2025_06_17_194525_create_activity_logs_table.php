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
        Schema::create('activity_logs', function (Blueprint $table) {
            $table->id();
            $table->string('activity_type'); // Por ejemplo: 'user_registered', 'product_updated', 'order_processed'
            $table->text('description');     // Descripción legible de la actividad
            $table->unsignedBigInteger('user_id')->nullable(); // ID del usuario que realizó la actividad (si aplica)
            $table->string('performed_by');  // Nombre o rol de quien realizó la actividad (ej. "Juan Pérez", "Sistema")
            $table->string('status')->default('completed'); // 'completed', 'pending', 'failed', 'info', etc.
            $table->json('data')->nullable(); // Almacenar datos adicionales como un JSON (ej. {product_id: 1, old_value: 'xyz'})
            $table->timestamps();

            // Opcional: Si quieres una relación con la tabla de usuarios
            $table->foreign('user_id')->references('id')->on('users')->onDelete('set null');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('activity_logs');
    }
};
