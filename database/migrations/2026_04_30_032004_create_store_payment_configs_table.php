<?php

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
        Schema::create('store_payment_configs', function (Blueprint $table) {
            $table->id();
            $table->foreignId('store_id')->constrained('stores')->cascadeOnDelete();
            $table->string('gateway', 30);           // webpay | mercadopago
            $table->string('environment', 20);       // integration/sandbox | production
            $table->text('credentials');             // JSON encriptado
            $table->boolean('active')->default(true);
            $table->timestamps();

            $table->unique(['store_id', 'gateway']); // una config por gateway por tienda
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('store_payment_configs');
    }
};
