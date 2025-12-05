<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('orders', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->string('customer_name', 255);
            $table->enum('status', ['draft', 'pending', 'paid', 'cancelled'])->default('draft');
            $table->decimal('subtotal', 15, 2)->default(0);
            $table->decimal('discount', 15, 2)->nullable()->default(0);
            $table->decimal('tax', 15, 2)->nullable()->default(0);
            $table->decimal('total', 15, 2)->default(0);
            $table->json('notes')->nullable();
            $table->softDeletes();
            $table->timestamps();

            // Índices para otimização de consultas
            $table->index('status');
            $table->index('customer_name');
            $table->index('created_at');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('orders');
    }
};

