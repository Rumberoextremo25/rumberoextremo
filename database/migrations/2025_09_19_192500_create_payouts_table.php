<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('payouts', function (Blueprint $table) {
            $table->id();
            
            // Foreign keys (sin constraint inicial)
            $table->unsignedBigInteger('sale_id');
            $table->unsignedBigInteger('ally_id');
            
            // Amount fields
            $table->decimal('sale_amount', 10, 2);
            $table->decimal('commission_percentage', 5, 2);
            $table->decimal('commission_amount', 10, 2);
            
            // Status and dates
            $table->string('status', 20)->default('pending');
            $table->timestamp('generation_date');
            $table->timestamp('payment_date')->nullable();
            
            // References
            $table->string('sale_reference', 100)->nullable();
            $table->string('payment_reference', 100)->nullable();
            
            // Payment details
            $table->string('ally_payment_method', 50)->default('transfer');
            $table->string('ally_account_number', 50)->nullable();
            $table->string('ally_bank', 50)->nullable();
            
            // Proof and notes
            $table->string('payment_proof')->nullable();
            $table->text('notes')->nullable();
            
            // Timestamps
            $table->timestamps();
            
            // Indexes
            $table->index('sale_id');
            $table->index('ally_id');
            $table->index('status');
            $table->index('generation_date');
            $table->index('payment_date');
            $table->index('sale_reference');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('payouts');
    }
};