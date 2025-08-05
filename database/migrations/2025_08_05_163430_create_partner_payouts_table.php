<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreatePartnerPayoutsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('partner_payouts', function (Blueprint $table) {
            $table->id();
            $table->foreignId('order_id')->constrained()->onDelete('cascade'); // Relaciona con la orden original
            $table->foreignId('partner_id')->constrained()->onDelete('cascade'); // Relaciona con el aliado
            $table->decimal('amount', 10, 2); // Monto a pagar al aliado
            $table->decimal('commission_amount', 10, 2)->default(0.00); // Monto que Rumbero Extremo retuvo
            $table->string('status')->default('pending'); // 'pending', 'processed', 'failed', 'cancelled'
            $table->string('bank_name')->nullable(); // Nombre del banco del aliado
            $table->string('account_number')->nullable(); // Número de cuenta del aliado
            $table->string('account_type')->nullable(); // Tipo de cuenta (corriente, ahorro)
            $table->string('id_document')->nullable(); // C.I. o RIF del aliado
            $table->string('transaction_reference')->nullable(); // Referencia de la transferencia bancaria (una vez realizada)
            $table->timestamp('processed_at')->nullable(); // Fecha y hora en que se realizó la transferencia
            $table->text('notes')->nullable(); // Notas adicionales
            $table->timestamps(); // created_at, updated_at
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('partner_payouts');
    }
}
