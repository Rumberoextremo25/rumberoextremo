<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::create('chat_messages', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->nullable()->constrained()->nullOnDelete();
            $table->string('session_id'); // Para usuarios no logueados
            $table->text('message');
            $table->enum('sender', ['user', 'admin']);
            $table->enum('status', ['pending', 'read', 'answered'])->default('pending');
            $table->timestamp('read_at')->nullable();
            $table->timestamp('answered_at')->nullable();
            $table->foreignId('answered_by')->nullable()->constrained('users');
            $table->timestamps();
            
            $table->index(['session_id', 'status']);
            $table->index('user_id');
            $table->index('created_at');
        });
    }

    public function down()
    {
        Schema::dropIfExists('chat_messages');
    }
};
