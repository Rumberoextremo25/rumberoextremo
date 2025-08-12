<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateUserC2pDetailsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('user_c2p_details', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->unique(); // Foreign key a la tabla users, y es única
            $table->string('encrypted_phone_number'); // Almacena el número encriptado
            $table->string('encrypted_id_card');      // Almacena la cédula encriptada
            $table->string('bank_code', 10);
            $table->string('account_type', 10);
            $table->timestamps(); // created_at y updated_at
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('user_c2p_details');
    }
}
