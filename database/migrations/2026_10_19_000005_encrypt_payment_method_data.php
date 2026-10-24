<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\Crypt;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /** Campos que deben quedar cifrados en reposo. */
    private array $fields = [
        'debtor_cell_phone',
        'debtor_id',
        'debtor_account',
        'card_expiration',
        'card_holder_name',
        'card_holder_id',
    ];

    public function up(): void
    {
        // El texto cifrado ocupa más que el valor original.
        foreach ($this->fields as $field) {
            if (Schema::hasColumn('users', $field)) {
                DB::statement("ALTER TABLE users ALTER COLUMN {$field} TYPE text");
            }
        }

        // Cifra valores que todavía estuvieran en texto plano.
        DB::table('users')->orderBy('id')->each(function ($user) {
            $changes = [];

            foreach ($this->fields as $field) {
                $value = $user->{$field} ?? null;
                if ($value === null || $value === '') {
                    continue;
                }

                try {
                    Crypt::decryptString($value);
                    // Ya estaba cifrado.
                } catch (\Throwable $e) {
                    $changes[$field] = Crypt::encryptString((string) $value);
                }
            }

            if ($changes !== []) {
                DB::table('users')->where('id', $user->id)->update($changes);
            }
        });

        // Elimina las copias planas de compatibilidad que ya no se usan.
        DB::table('users')->update([
            'payment_bank_name' => null,
            'payment_phone' => null,
            'payment_holder_name' => null,
            'payment_holder_id' => null,
            'payment_debit_account' => null,
            'payment_debit_bank_name' => null,
            'payment_card_expiry' => null,
            'payment_card_holder' => null,
        ]);
    }

    public function down(): void
    {
        // No se revierte el cifrado para no exponer datos.
    }
};
