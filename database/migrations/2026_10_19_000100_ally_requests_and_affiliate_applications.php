<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * - Estado de revisión para las solicitudes de locales (ally_contacts).
 * - Tabla de solicitudes de afiliado (rumberos): el formulario público la usaba
 *   pero nunca se creó, así que fallaba con un error de SQL.
 */
return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasTable('ally_contacts') && ! Schema::hasColumn('ally_contacts', 'status')) {
            Schema::table('ally_contacts', function (Blueprint $table) {
                $table->string('status', 20)->default('pendiente')->after('message');
                $table->text('admin_notes')->nullable()->after('status');
                $table->timestamp('reviewed_at')->nullable()->after('admin_notes');
                $table->foreignId('reviewed_by')->nullable()->after('reviewed_at')
                    ->constrained('users')->nullOnDelete();
            });
        }

        if (! Schema::hasTable('affiliate_applications')) {
            Schema::create('affiliate_applications', function (Blueprint $table) {
                $table->id();
                $table->string('full_name');
                $table->string('email')->unique();
                $table->string('phone', 30)->nullable();
                $table->string('social_media_link');
                $table->unsignedInteger('followers_count')->default(0);
                $table->string('affiliate_type', 40);
                $table->text('message')->nullable();
                $table->boolean('terms')->default(true);
                $table->string('status', 20)->default('pendiente');
                $table->text('admin_notes')->nullable();
                $table->timestamp('reviewed_at')->nullable();
                $table->foreignId('reviewed_by')->nullable()->constrained('users')->nullOnDelete();
                $table->timestamps();
            });
        }

        if (Schema::hasTable('affiliate_applications') && ! Schema::hasColumn('affiliate_applications', 'status')) {
            Schema::table('affiliate_applications', function (Blueprint $table) {
                $table->string('status', 20)->default('pendiente');
            });
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('affiliate_applications');

        if (Schema::hasColumn('ally_contacts', 'status')) {
            Schema::table('ally_contacts', function (Blueprint $table) {
                $table->dropColumn(['status', 'admin_notes', 'reviewed_at', 'reviewed_by']);
            });
        }
    }
};
