<?php
// database/migrations/2026_03_01_194000_add_is_featured_to_promotions_table.php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('promotions', function (Blueprint $table) {
            $table->boolean('is_featured')
                  ->default(false)
                  ->after('status')
                  ->comment('Indica si la promoción es destacada');
        });
    }

    public function down(): void
    {
        Schema::table('promotions', function (Blueprint $table) {
            $table->dropColumn('is_featured');
        });
    }
};
