<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

/**
 * El chat ahora guarda también los mensajes de la IA (sender = 'ai').
 * La restricción original solo permitía 'user' y 'admin'.
 */
return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasTable('chat_messages')) {
            return;
        }

        // PostgreSQL: se elimina y recrea el CHECK con los nuevos valores
        DB::statement('ALTER TABLE chat_messages DROP CONSTRAINT IF EXISTS chat_messages_sender_check');
        DB::statement("ALTER TABLE chat_messages ADD CONSTRAINT chat_messages_sender_check CHECK (sender IN ('user','admin','ai','system'))");
    }

    public function down(): void
    {
        if (! Schema::hasTable('chat_messages')) {
            return;
        }

        DB::statement('ALTER TABLE chat_messages DROP CONSTRAINT IF EXISTS chat_messages_sender_check');
        DB::statement("ALTER TABLE chat_messages ADD CONSTRAINT chat_messages_sender_check CHECK (sender IN ('user','admin'))");
    }
};
