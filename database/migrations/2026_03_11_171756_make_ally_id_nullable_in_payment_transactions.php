<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class MakeAllyIdNullableInPaymentTransactions extends Migration
{
    public function up(): void
    {
        Schema::table('payment_transactions', function (Blueprint $table) {
            // Primero eliminamos la foreign key
            $table->dropForeign(['ally_id']);
            
            // Luego modificamos la columna para que sea nullable
            $table->foreignId('ally_id')->nullable()->change();
            
            // Finalmente, recreamos la foreign key
            $table->foreign('ally_id')->references('id')->on('allies')->onDelete('cascade');
        });
    }

    public function down(): void
    {
        Schema::table('payment_transactions', function (Blueprint $table) {
            // Revertir los cambios
            $table->dropForeign(['ally_id']);
            $table->foreignId('ally_id')->nullable(false)->change();
            $table->foreign('ally_id')->references('id')->on('allies')->onDelete('cascade');
        });
    }
}