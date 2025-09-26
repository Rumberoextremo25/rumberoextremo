<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('allies', function (Blueprint $table) {
            // Add commission field
            $table->decimal('commission_percentage', 5, 2)->default(0)->after('discount');
            
            // Add payment method field
            $table->string('default_payment_method', 20)->default('transfer')->after('commission_percentage');
            
            // Add banking fields (alternative to existing ones)
            $table->string('bank_account', 50)->nullable()->after('default_payment_method');
            $table->string('bank', 50)->nullable()->after('bank_account');
            
            // Add document field
            $table->string('id_document', 20)->nullable()->after('bank');
            
            // Add index for commission percentage (CON NOMBRE EXPLÍCITO)
            $table->index('commission_percentage', 'allies_commission_percentage_index');
            $table->index('default_payment_method', 'allies_payment_method_index');
        });
    }

    public function down(): void
    {
        Schema::table('allies', function (Blueprint $table) {
            // Eliminar índices por nombre explícito
            $table->dropIndex('allies_commission_percentage_index');
            $table->dropIndex('allies_payment_method_index');
            
            // Eliminar columnas
            $table->dropColumn([
                'commission_percentage',
                'default_payment_method',
                'bank_account',
                'bank',
                'id_document'
            ]);
        });
    }
};