<?php
// database/migrations/xxxx_xx_xx_xxxxxx_make_ally_id_nullable_in_promotions_table.php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class MakeAllyIdNullableInPromotionsTable extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('promotions', function (Blueprint $table) {
            // Eliminar la llave foránea primero
            $table->dropForeign(['ally_id']);
            
            // Hacer la columna nullable
            $table->unsignedBigInteger('ally_id')->nullable()->change();
            
            // Volver a agregar la llave foránea (ahora permite null)
            $table->foreign('ally_id')
                  ->references('id')
                  ->on('allies')
                  ->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('promotions', function (Blueprint $table) {
            // Revertir los cambios
            $table->dropForeign(['ally_id']);
            $table->unsignedBigInteger('ally_id')->nullable(false)->change();
            $table->foreign('ally_id')
                  ->references('id')
                  ->on('allies')
                  ->onDelete('cascade');
        });
    }
}
