<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasColumn('users', 'debtor_bank_code')) {
            DB::statement(
                "ALTER TABLE users ALTER COLUMN debtor_bank_code TYPE varchar(4) USING LPAD(debtor_bank_code::text, 4, '0')"
            );
        }
    }

    public function down(): void
    {
        if (Schema::hasColumn('users', 'debtor_bank_code')) {
            DB::statement(
                'ALTER TABLE users ALTER COLUMN debtor_bank_code TYPE integer USING NULLIF(debtor_bank_code, \'\')::integer'
            );
        }
    }
};
