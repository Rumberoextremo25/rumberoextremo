<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up()
    {
        Schema::create('ally_types', function (Blueprint $table) {
            $table->id();
            $table->string('name')->unique(); // Ej: 'Logística / Producción', 'Local / Venue'
            $table->string('description')->nullable(); // Opcional: una descripción más detallada
            $table->boolean('is_active')->default(true); // Para activar/desactivar tipos
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('ally_types');
    }
};
