<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            if (!Schema::hasColumn('users', 'payment_bank_code')) {
                $table->string('payment_bank_code', 10)->nullable();
            }
            if (!Schema::hasColumn('users', 'payment_bank_name')) {
                $table->string('payment_bank_name', 100)->nullable();
            }
            if (!Schema::hasColumn('users', 'payment_phone')) {
                $table->string('payment_phone', 20)->nullable();
            }
            if (!Schema::hasColumn('users', 'payment_holder_name')) {
                $table->string('payment_holder_name')->nullable();
            }
            if (!Schema::hasColumn('users', 'payment_holder_id')) {
                $table->string('payment_holder_id', 50)->nullable();
            }
            if (!Schema::hasColumn('users', 'payment_debit_bank_code')) {
                $table->string('payment_debit_bank_code', 10)->nullable();
            }
            if (!Schema::hasColumn('users', 'payment_debit_bank_name')) {
                $table->string('payment_debit_bank_name', 100)->nullable();
            }
            if (!Schema::hasColumn('users', 'payment_debit_account')) {
                $table->text('payment_debit_account')->nullable();
            }
            if (!Schema::hasColumn('users', 'payment_debit_account_type')) {
                $table->string('payment_debit_account_type', 20)->default('CELE');
            }
            if (!Schema::hasColumn('users', 'payment_card_last4')) {
                $table->string('payment_card_last4', 4)->nullable();
            }
            if (!Schema::hasColumn('users', 'payment_card_expiry')) {
                $table->string('payment_card_expiry', 7)->nullable();
            }
            if (!Schema::hasColumn('users', 'payment_card_holder')) {
                $table->string('payment_card_holder')->nullable();
            }
            if (!Schema::hasColumn('users', 'payment_updated_at')) {
                $table->timestamp('payment_updated_at')->nullable();
            }
        });
    }

    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn([
                'payment_bank_code',
                'payment_bank_name',
                'payment_phone',
                'payment_holder_name',
                'payment_holder_id',
                'payment_debit_bank_code',
                'payment_debit_bank_name',
                'payment_debit_account',
                'payment_debit_account_type',
                'payment_card_last4',
                'payment_card_expiry',
                'payment_card_holder',
                'payment_updated_at',
            ]);
        });
    }
};
