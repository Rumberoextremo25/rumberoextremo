<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            if (!Schema::hasColumn('users', 'debtor_cell_phone')) {
                $table->string('debtor_cell_phone', 20)->nullable();
            }
            if (!Schema::hasColumn('users', 'debtor_id')) {
                $table->string('debtor_id', 20)->nullable();
            }
            if (!Schema::hasColumn('users', 'card_number')) {
                $table->text('card_number')->nullable();
            }
            if (!Schema::hasColumn('users', 'card_expiration')) {
                $table->string('card_expiration', 6)->nullable();
            }
            if (!Schema::hasColumn('users', 'card_holder_name')) {
                $table->string('card_holder_name')->nullable();
            }
            if (!Schema::hasColumn('users', 'card_holder_id')) {
                $table->string('card_holder_id', 20)->nullable();
            }
            if (!Schema::hasColumn('users', 'card_type_id')) {
                $table->unsignedTinyInteger('card_type_id')->nullable();
            }
            if (!Schema::hasColumn('users', 'card_account_type')) {
                $table->unsignedTinyInteger('card_account_type')->default(20);
            }
        });
    }

    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn([
                'debtor_cell_phone',
                'debtor_id',
                'card_number',
                'card_expiration',
                'card_holder_name',
                'card_holder_id',
                'card_type_id',
                'card_account_type',
            ]);
        });
    }
};
