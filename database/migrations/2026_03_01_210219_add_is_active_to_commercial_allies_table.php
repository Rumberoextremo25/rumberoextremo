<?php
// database/migrations/2026_03_01_200000_add_is_active_to_commercial_allies_table.php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('commercial_allies', function (Blueprint $table) {
            $table->boolean('is_active')->default(true)->after('website_url');
        });
    }

    public function down(): void
    {
        Schema::table('commercial_allies', function (Blueprint $table) {
            $table->dropColumn('is_active');
        });
    }
};
