<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('newsletter_subscribers', function (Blueprint $table) {
            $table->string('ip_address', 45)->nullable()->after('email');
            $table->text('user_agent')->nullable()->after('ip_address');
            $table->timestamp('confirmed_at')->nullable()->after('user_agent');
            $table->string('confirmation_token', 64)->nullable()->after('confirmed_at');
            $table->timestamp('subscribed_at')->nullable()->after('confirmation_token');
            
            // Índice para búsquedas rápidas por token
            $table->index('confirmation_token');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('newsletter_subscribers', function (Blueprint $table) {
            $table->dropColumn([
                'ip_address',
                'user_agent',
                'confirmed_at',
                'confirmation_token',
                'subscribed_at'
            ]);
            
            $table->dropIndex(['confirmation_token']);
        });
    }
};
