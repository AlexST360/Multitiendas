<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('payments', function (Blueprint $table) {
            $table->id();

            // Multi-store
            $table->foreignId('store_id')->constrained()->cascadeOnDelete();

            // Relación orden
            $table->foreignId('order_id')->constrained()->cascadeOnDelete();

            // Gateway info
            $table->string('gateway'); // webpay | mercadopago | fake
            $table->string('gateway_reference')->nullable();

            // Estado
            $table->string('status');

            // Monto
            $table->string('currency', 3);
            $table->unsignedBigInteger('amount');

            // Idempotencia
            $table->string('idempotency_key');

            // Payloads crudos
            $table->json('raw_response')->nullable();

            $table->timestamps();

            //  Índices importantes
            $table->unique(['store_id', 'idempotency_key']);

            //  Índice clave para consultas por orden (multi-tenant)
            $table->index(['store_id', 'order_id']);

            //  Índice útil para tracking por gateway
            $table->index(['store_id', 'gateway', 'gateway_reference']);

            // (Opcional PRO) si el gateway_reference es único por gateway/store:
            // $table->unique(['store_id', 'gateway', 'gateway_reference']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('payments');
    }
};