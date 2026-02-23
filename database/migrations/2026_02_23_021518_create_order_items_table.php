<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('order_items', function (Blueprint $table) {
            $table->id();

            $table->foreignId('store_id')->constrained('stores')->cascadeOnDelete();
            $table->foreignId('order_id')->constrained('orders')->cascadeOnDelete();

            // Referencia al producto (opcional mantener FK; útil para trazabilidad)
            $table->foreignId('product_id')->constrained('products')->restrictOnDelete();

            // Snapshot sagrado
            $table->string('name');
            $table->string('sku')->nullable();
            $table->unsignedBigInteger('unit_price'); // entero
            $table->unsignedInteger('qty');
            $table->unsignedBigInteger('line_total');

            $table->timestamps();

            $table->index(['store_id', 'order_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('order_items');
    }
};