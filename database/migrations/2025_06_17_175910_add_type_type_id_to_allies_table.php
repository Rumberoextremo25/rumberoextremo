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
        Schema::table('allies', function (Blueprint $table) {
            // Añadir la columna de clave foránea
            $table->foreignId('ally_type_id')->nullable()->constrained('ally_types')->onDelete('set null')->after('type'); // Opcional si type es ya tu FK
            // Si ya tienes la columna 'type' y quieres que ahora sea la FK:
            // $table->dropColumn('type'); // Elimina la columna string si la vas a reemplazar con la FK
            // $table->foreignId('ally_type_id')->constrained()->after('name'); // Añade la FK
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down(): void
    {
        Schema::table('allies', function (Blueprint $table) {
            $table->dropForeign(['ally_type_id']);
            $table->dropColumn('ally_type_id');
            // Si eliminaste 'type', podrías añadirla de nuevo aquí si lo deseas
            // $table->string('type')->nullable();
        });
    }
};