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
        Schema::create('orders', function (Blueprint $table) {
            $table->id();
            // $table->foreignId('user_id')->nullable()->constrained()->onDelete('set null'); // Si las órdenes están relacionadas con usuarios
            $table->decimal('total', 10, 2); // Monto total de la venta, 10 dígitos en total, 2 decimales
            $table->string('status')->default('completed'); // Ejemplo: completed, pending, cancelled
            // Puedes añadir más campos como fecha de envío, productos asociados, etc.
            $table->timestamps(); // created_at (fecha de la venta), updated_at
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('orders');
    }
};
