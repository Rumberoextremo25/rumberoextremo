<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('clients', function (Blueprint $table) {
            $table->id();
            
            // Client information
            $table->string('name');
            $table->string('document_type', 2)->default('V');
            $table->string('id_number', 20);
            
            // Contact information
            $table->string('phone', 15)->nullable();
            $table->string('email')->nullable();
            $table->text('address')->nullable();
            
            // Status
            $table->string('status', 20)->default('active');
            
            // Timestamps
            $table->timestamps();
            
            // Indexes
            $table->index('id_number');
            $table->index('document_type');
            $table->index('status');
            $table->unique(['document_type', 'id_number']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('clients');
    }
};