<?php

namespace App\Http\Controllers;

use App\Models\Product;
use App\Models\Category;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class ProductController extends Controller
{
    public function index()
    {
        $q = request()->get('q');
        $categoryId = request()->get('category_id');

        // Only show sellable products (not supplies)
        $query = Product::with('categoryModel')->withCount('supplies')
            ->where('type', 'product')
            ->latest();

        if ($q) {
            $query->where('name', 'like', "%{$q}%");
        }

        if ($categoryId) {
            $query->where('category_id', $categoryId);
        }

        $products = $query->paginate(10)->appends(request()->query());

        // pass categories for the filter select (only for products, not supplies)
        $categories = Category::where('is_active', true)
            ->whereNotIn('slug', ['supply'])
            ->orderBy('name')
            ->get();

        return view('products.index', compact('products', 'categories', 'q', 'categoryId'));
    }

    public function create()
    {
        // Only show categories for sellable products (not supply category)
        $categories = Category::where('is_active', true)
            ->whereNotIn('slug', ['supply'])
            ->orderBy('name')
            ->get();

        return view('products.create', compact('categories'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'category_id' => 'required|exists:categories,id',
            'item_type' => 'required|in:sellable',
            'price' => 'required|numeric|min:0',
            'description' => 'nullable|string',
            'image' => 'nullable|image|max:2048',
            'is_active' => 'boolean',
        ]);

        $category = Category::findOrFail($validated['category_id']);
        $validated['category'] = $category->slug;
        $validated['type'] = 'product'; // Always a product for this controller
        $validated['price'] = $validated['price'] ?? 0;
        $validated['stock'] = 0; // Stock managed in Inventory
        $validated['low_stock_threshold'] = 10; // Default threshold

        if ($request->hasFile('image')) {
            $validated['image'] = $request->file('image')->store('products', 'public');
        }

        $validated['is_active'] = $request->has('is_active');

        Product::create($validated);

        return redirect()->route('products.index')
            ->with('success', 'Product created successfully.');
    }

    public function show(Product $product)
    {
        return view('products.show', compact('product'));
    }

    public function edit(Product $product)
    {
        $categories = Category::where('is_active', true)->orderBy('name')->get();

        return view('products.edit', compact('product', 'categories'));
    }

    public function update(Request $request, Product $product)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'category_id' => 'required|exists:categories,id',
            'item_type' => 'required|in:sellable',
            'price' => 'required|numeric|min:0',
            'description' => 'nullable|string',
            'image' => 'nullable|image|max:2048',
            'is_active' => 'boolean',
        ]);

        $category = Category::findOrFail($validated['category_id']);
        $validated['category'] = $category->slug;
        $validated['type'] = 'product'; // Always a product for this controller
        $validated['price'] = $validated['price'] ?? 0;

        if ($request->hasFile('image')) {
            // Delete old image
            if ($product->image) {
                Storage::disk('public')->delete($product->image);
            }
            $validated['image'] = $request->file('image')->store('products', 'public');
        }

        $validated['is_active'] = $request->has('is_active');

        $product->update($validated);

        return redirect()->route('products.index')
            ->with('success', 'Product updated successfully.');
    }

    public function destroy(Product $product)
    {
        if ($product->image) {
            Storage::disk('public')->delete($product->image);
        }

        $product->delete();

        return redirect()->route('products.index')
            ->with('success', 'Product deleted successfully.');
    }
}
