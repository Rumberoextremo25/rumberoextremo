<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // Agregar foreign keys a la tabla sales
        Schema::table('sales', function (Blueprint $table) {
            $table->foreign('client_id')->references('id')->on('clients')->onDelete('cascade');
            $table->foreign('branch_id')->references('id')->on('branches')->onDelete('cascade');
            $table->foreign('ally_id')->references('id')->on('allies')->onDelete('cascade');
        });

        // Agregar foreign keys a la tabla payouts
        Schema::table('payouts', function (Blueprint $table) {
            $table->foreign('sale_id')->references('id')->on('sales')->onDelete('cascade');
            $table->foreign('ally_id')->references('id')->on('allies')->onDelete('cascade');
        });
    }

    public function down(): void
    {
        // Remover foreign keys de sales
        Schema::table('sales', function (Blueprint $table) {
            $table->dropForeign(['client_id']);
            $table->dropForeign(['branch_id']);
            $table->dropForeign(['ally_id']);
        });

        // Remover foreign keys de payouts
        Schema::table('payouts', function (Blueprint $table) {
            $table->dropForeign(['sale_id']);
            $table->dropForeign(['ally_id']);
        });
    }
};