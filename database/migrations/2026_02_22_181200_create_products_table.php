<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('products', function (Blueprint $table) {

            $table->id();

            // Multi-store obligatorio
            $table->foreignId('store_id')
                ->constrained('stores')
                ->cascadeOnDelete();

            $table->string('name');
            $table->string('slug');
            $table->text('description')->nullable();

            // CLP sin decimales
            $table->unsignedInteger('price');

            $table->unsignedInteger('stock')->default(0);

            $table->string('sku')->nullable();
            $table->boolean('active')->default(true);

            $table->timestamps();

            // Slug único por tienda
            $table->unique(['store_id', 'slug']);

            // Índices útiles
            $table->index(['store_id', 'active']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('products');
    }
};