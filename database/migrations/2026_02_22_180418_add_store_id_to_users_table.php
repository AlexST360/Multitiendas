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
        Schema::table('users', function (Blueprint $table) {

            // 1️⃣ Agregamos la columna store_id
            $table->foreignId('store_id')
                ->after('id')
                ->constrained('stores') // referencia a stores.id
                ->cascadeOnDelete();    // si se elimina la tienda, se eliminan sus usuarios

        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {

            // 2️⃣ Eliminamos la FK + columna correctamente
            $table->dropConstrainedForeignId('store_id');

        });
    }
};