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

            // Foreign keys
            $table->foreignId('sale_id')->constrained('sales')->onDelete('cascade');
            $table->foreignId('ally_id')->constrained('allies')->onDelete('cascade');

            // Amount fields
            $table->decimal('sale_amount', 12, 2)->default(0);
            $table->decimal('commission_percentage', 5, 2)->default(0);
            $table->decimal('commission_amount', 12, 2)->default(0);
            $table->decimal('net_amount', 12, 2)->default(0);

            // Status
            $table->enum('status', ['pending', 'completed', 'failed'])->default('pending');
            $table->timestamp('generation_date')->useCurrent();
            $table->timestamp('payment_date')->nullable();

            // References
            $table->string('sale_reference', 100)->nullable();

            // Timestamps
            $table->timestamps();

            // Indexes
            $table->index('sale_id');
            $table->index('ally_id');
            $table->index('status');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('payouts');
    }
};
