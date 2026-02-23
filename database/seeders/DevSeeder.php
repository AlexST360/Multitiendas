<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;
use App\Models\Store;
use App\Models\User;
use App\Models\Product;

class DevSeeder extends Seeder
{
    public function run(): void
    {
        $store = Store::create([
            'name' => 'Tienda Demo',
            'slug' => 'tienda-demo',
        ]);

        $user = User::create([
            'name' => 'Admin Demo',
            'email' => 'admin@demo.cl',
            'password' => Hash::make('password'),
            'store_id' => $store->id,
        ]);

        Product::create([
            'store_id' => $store->id,
            'name' => 'Producto Demo',
            'slug' => 'producto-demo',
            'description' => 'Producto de prueba',
            'price' => 9990,
            'stock' => 10,
            'sku' => 'SKU-DEMO',
            'active' => true,
        ]);
    }
}