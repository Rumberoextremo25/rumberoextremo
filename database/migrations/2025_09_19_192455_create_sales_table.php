<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('sales', function (Blueprint $table) {
            $table->id();
            
            // Foreign keys (sin constraint inicial)
            $table->unsignedBigInteger('client_id');
            $table->unsignedBigInteger('branch_id');
            $table->unsignedBigInteger('ally_id');
            
            // Amount fields
            $table->decimal('total_amount', 10, 2);
            $table->decimal('paid_amount', 10, 2);
            
            // Payment information
            $table->string('payment_method', 50);
            $table->string('bank_reference', 100)->nullable();
            $table->string('transaction_id', 100)->nullable();
            $table->string('status', 20)->default('completed');
            
            // Dates
            $table->timestamp('sale_date');
            $table->timestamp('payment_date')->nullable();
            
            // Client and transaction details
            $table->string('terminal', 50)->nullable();
            $table->integer('destination_bank')->nullable();
            $table->string('client_phone', 15)->nullable();
            $table->string('client_id_number', 20)->nullable();
            
            // Additional information
            $table->text('description')->nullable();
            $table->string('authorization_code', 100)->nullable();
            $table->json('bank_response')->nullable();
            
            // Timestamps
            $table->timestamps();
            
            // Indexes
            $table->index('bank_reference');
            $table->index('transaction_id');
            $table->index('sale_date');
            $table->index('status');
            $table->index(['ally_id', 'sale_date']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('sales');
    }
};