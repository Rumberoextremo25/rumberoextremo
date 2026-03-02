<?php
// database/migrations/2026_03_01_220000_add_default_value_to_activity_type_in_activity_logs.php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('activity_logs', function (Blueprint $table) {
            $table->string('activity_type')->default('general')->change();
        });
    }

    public function down(): void
    {
        Schema::table('activity_logs', function (Blueprint $table) {
            $table->string('activity_type')->default(null)->change();
        });
    }
};
