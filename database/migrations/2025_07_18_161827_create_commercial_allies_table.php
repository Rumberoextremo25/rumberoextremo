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
        Schema::create('commercial_allies', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('logo_url'); // Campo para la URL del logo
            $table->double('rating', 2, 1)->default(0.0); // Calificación (ej. 4.5)
            $table->text('description')->nullable();
            $table->string('website_url')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('commercial_allies');
    }
};
