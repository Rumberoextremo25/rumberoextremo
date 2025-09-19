<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('branches', function (Blueprint $table) {
            $table->id();
            
            // Branch information
            $table->string('name');
            $table->string('code', 10)->unique();
            $table->text('address');
            $table->string('phone', 15)->nullable();
            $table->string('manager')->nullable();
            
            // Status
            $table->string('status', 20)->default('active');
            
            // Timestamps
            $table->timestamps();
            
            // Indexes
            $table->index('code');
            $table->index('status');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('branches');
    }
};