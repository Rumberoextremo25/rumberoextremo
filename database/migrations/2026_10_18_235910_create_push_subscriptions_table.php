<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Suscripciones Web Push (una por dispositivo/navegador) y preferencia por
 * usuario para recibir las notificaciones del panel como push.
 */
return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasTable('push_subscriptions')) {
            Schema::create('push_subscriptions', function (Blueprint $table) {
                $table->id();
                $table->foreignId('user_id')->constrained()->cascadeOnDelete();
                $table->string('endpoint', 500);
                $table->string('public_key');       // p256dh
                $table->string('auth_token');       // auth
                $table->string('content_encoding', 20)->default('aes128gcm');
                $table->string('user_agent', 255)->nullable();
                $table->timestamps();

                $table->unique(['user_id', 'endpoint']);
            });
        }

        if (! Schema::hasColumn('users', 'push_enabled')) {
            Schema::table('users', function (Blueprint $table) {
                $table->boolean('push_enabled')->default(false)->after('notifications_enabled');
            });
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('push_subscriptions');

        if (Schema::hasColumn('users', 'push_enabled')) {
            Schema::table('users', function (Blueprint $table) {
                $table->dropColumn('push_enabled');
            });
        }
    }
};
