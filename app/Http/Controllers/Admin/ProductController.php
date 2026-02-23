<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\StoreProductRequest;
use App\Http\Requests\Admin\UpdateProductRequest;
use App\Models\Product;
use App\Models\ProductImage;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Illuminate\View\View;

class ProductController extends Controller
{
    public function index(): View
    {
        $storeId = Auth::user()->store_id;

        $products = Product::forStore($storeId)
            ->with(['primaryImage']) // ✅ para mostrar miniatura en listado
            ->latest()
            ->get();

        return view('admin.products.index', compact('products'));
    }

    public function create(): View
    {
        return view('admin.products.create');
    }

    public function store(StoreProductRequest $request): RedirectResponse
    {
        $storeId = Auth::user()->store_id;

        $baseSlug = Str::slug($request->name);
        $slug = $baseSlug;

        $i = 2;
        while (Product::where('store_id', $storeId)->where('slug', $slug)->exists()) {
            $slug = "{$baseSlug}-{$i}";
            $i++;
        }

        Product::create([
            'store_id' => $storeId,
            'name' => $request->name,
            'slug' => $slug,
            'description' => $request->description,
            'price' => $request->price,
            'stock' => $request->stock,
            'sku' => $request->sku,
            'active' => $request->active,
        ]);

        return redirect()
            ->route('admin.products.index')
            ->with('status', 'Producto creado');
    }

    public function edit(Product $product): View
    {
        $storeId = Auth::user()->store_id;

        abort_unless($product->store_id === $storeId, 404);

        // ✅ Traer imágenes para mostrar en la vista de edición
        $product->load('images');

        return view('admin.products.edit', compact('product'));
    }

    public function update(UpdateProductRequest $request, Product $product): RedirectResponse
    {
        $storeId = Auth::user()->store_id;

        abort_unless($product->store_id === $storeId, 404);

        // Si cambió el nombre, regeneramos slug (único por tienda)
        $newBaseSlug = Str::slug($request->name);

        if ($newBaseSlug !== $product->slug) {
            $slug = $newBaseSlug;
            $i = 2;

            while (
                Product::where('store_id', $storeId)
                    ->where('slug', $slug)
                    ->where('id', '!=', $product->id)
                    ->exists()
            ) {
                $slug = "{$newBaseSlug}-{$i}";
                $i++;
            }

            $product->slug = $slug;
        }

        $product->fill([
            'name' => $request->name,
            'description' => $request->description,
            'price' => $request->price,
            'stock' => $request->stock,
            'sku' => $request->sku,
            'active' => $request->active,
        ]);

        $product->save();

        return redirect()
            ->route('admin.products.index')
            ->with('status', 'Producto actualizado');
    }

    public function toggleActive(Product $product): RedirectResponse
    {
        $storeId = Auth::user()->store_id;

        abort_unless($product->store_id === $storeId, 404);

        $product->active = ! $product->active;
        $product->save();

        return redirect()
            ->route('admin.products.index')
            ->with('status', $product->active ? 'Producto activado' : 'Producto desactivado');
    }

    // ========= IMÁGENES =========

    public function storeImage(Request $request, Product $product): RedirectResponse
    {
        $storeId = Auth::user()->store_id;

        abort_unless($product->store_id === $storeId, 404);

        $request->validate([
            'image' => ['required', 'image', 'mimes:jpg,jpeg,png,webp', 'max:4096'],
        ]);

        $dir = "stores/{$storeId}/products/{$product->id}";
        $path = $request->file('image')->store($dir, 'public');

        $nextSort = (int) ($product->images()->max('sort') ?? 0) + 1;
        $isFirst = ! $product->images()->exists();

        ProductImage::create([
            'store_id' => $storeId,
            'product_id' => $product->id,
            'path' => $path,
            'sort' => $nextSort,
            'is_primary' => $isFirst,
        ]);

        return redirect()
            ->route('admin.products.edit', $product)
            ->with('status', 'Imagen subida');
    }

    public function destroyImage(Product $product, ProductImage $image): RedirectResponse
    {
        $storeId = Auth::user()->store_id;

        abort_unless($product->store_id === $storeId, 404);

        // Anti-trampa: imagen debe pertenecer a este producto y tienda
        abort_unless($image->product_id === $product->id && $image->store_id === $storeId, 404);

        Storage::disk('public')->delete($image->path);

        $wasPrimary = (bool) $image->is_primary;
        $image->delete();

        // Si borraron la primaria, asignar otra como primaria (la primera por sort)
        if ($wasPrimary) {
            $newPrimary = $product->images()->orderBy('sort')->first();
            if ($newPrimary) {
                $newPrimary->is_primary = true;
                $newPrimary->save();
            }
        }

        return redirect()
            ->route('admin.products.edit', $product)
            ->with('status', 'Imagen eliminada');
    }
}