<?php

namespace App\Http\Controllers;

use App\Models\Product;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Symfony\Component\HttpFoundation\StreamedResponse;
use Illuminate\Support\Str;

class ProductSupplyController extends Controller
{
    public function edit(Product $product)
    {
        // server-side pagination, search and sort for supplies
        $query = Product::where('category', 'supply')
            ->where('is_active', true);

        if ($search = request('search')) {
            $query->where('name', 'like', '%' . $search . '%');
        }

        $sort = in_array(request('sort'), ['name', 'stock']) ? request('sort') : 'name';
        $direction = request('direction') === 'desc' ? 'desc' : 'asc';

        $supplies = $query->orderBy($sort, $direction)->paginate(15)->appends(request()->query());

        $product->load('supplies');

        return view('products.supplies', compact('product', 'supplies', 'sort', 'direction', 'search'));
    }

    public function export(Product $product)
    {
        $headers = [
            'Content-Type' => 'text/csv',
            'Content-Disposition' => 'attachment; filename="product_'. $product->id .'_supplies.csv"',
        ];

        $callback = function () use ($product) {
            $handle = fopen('php://output', 'w');
            fputcsv($handle, ['product_id','supply_id','supply_name','quantity']);

            foreach ($product->supplies()->withPivot('quantity')->get() as $s) {
                fputcsv($handle, [$product->id, $s->id, $s->name, $s->pivot->quantity]);
            }

            fclose($handle);
        };

        return new StreamedResponse($callback, 200, $headers);
    }

    public function import(Request $request, Product $product)
    {
        $request->validate(['file' => 'required|file|mimes:csv,txt']);

        $path = $request->file('file')->getRealPath();
        $rows = array_map('str_getcsv', file($path));

        $sync = [];
        foreach ($rows as $i => $row) {
            if ($i === 0) continue; // skip header
            [$pid, $supplyId, $supplyName, $quantity] = array_pad($row, 4, null);
            $qty = (int) ($quantity ?? 0);
            if ($qty > 0 && is_numeric($supplyId)) {
                $sync[(int)$supplyId] = ['quantity' => $qty];
            }
        }

        $product->supplies()->sync($sync);

        if ($request->wantsJson()) {
            return response()->json(['success' => true]);
        }

        return back()->with('success', 'Imported supplies.');
    }

    public function update(Request $request, Product $product)
    {
        $data = $request->validate([
            'supplies' => 'nullable|array',
            'supplies.*.id' => 'required|integer|exists:products,id',
            'supplies.*.quantity' => 'nullable|integer|min:0',
        ]);

        $sync = [];

        if (!empty($data['supplies'])) {
            foreach ($data['supplies'] as $s) {
                $qty = isset($s['quantity']) ? (int) $s['quantity'] : 0;
                if ($qty > 0) {
                    $sync[$s['id']] = ['quantity' => $qty];
                }
            }
        }

        $product->supplies()->sync($sync);

        if ($request->wantsJson() || $request->expectsJson()) {
            return response()->json(['success' => true, 'message' => 'Supplies updated successfully.']);
        }

        return redirect()->route('products.edit', $product)
            ->with('success', 'Supplies updated successfully.');
    }
}
