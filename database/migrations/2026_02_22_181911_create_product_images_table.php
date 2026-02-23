<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('product_images', function (Blueprint $table) {

            $table->id();

            // Multi-store obligatorio
            $table->foreignId('store_id')
                ->constrained('stores')
                ->cascadeOnDelete();

            // Relación con producto
            $table->foreignId('product_id')
                ->constrained('products')
                ->cascadeOnDelete();

            // Path relativo dentro del disk public
            $table->string('path');

            // Orden visual
            $table->unsignedInteger('sort')->default(0);

            // Imagen principal (clave para storefront)
            $table->boolean('is_primary')->default(false);

            $table->timestamps();

            // Índices útiles
            $table->index(['store_id', 'product_id']);
            $table->index(['product_id', 'is_primary']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('product_images');
    }
};