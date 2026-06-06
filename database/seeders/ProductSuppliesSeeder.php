<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Product;
use Illuminate\Support\Str;

class ProductSuppliesSeeder extends Seeder
{
    public function run(): void
    {
        // find common supplies
        $cup16 = Product::where('category', 'supply')->where('name', 'like', '%16oz%')->first();
        $cup22 = Product::where('category', 'supply')->where('name', 'like', '%22oz%')->first();
        $strawRegular = Product::where('category', 'supply')->where('name', 'like', '%Straw (Regular)%')->first();
        $strawJumbo = Product::where('category', 'supply')->where('name', 'like', '%Jumbo%')->first();

        // target products: milk_tea category or names containing coffee/fruit/non-coffee
        $targets = Product::where(function ($q) {
            $q->where('category', 'milk_tea')
              ->orWhereRaw('LOWER(name) LIKE ?', ['%coffee%'])
              ->orWhereRaw('LOWER(name) LIKE ?', ['%fruit%'])
              ->orWhereRaw('LOWER(name) LIKE ?', ['%non coffee%']);
        })->get();

        foreach ($targets as $product) {
            $sync = [];

            // choose cup size by product name hints
            $lower = Str::lower($product->name);
            $use22 = false;
            if (Str::contains($lower, ['xl', '22oz', 'large', 'jumbo', 'extra'])) {
                $use22 = true;
            }

            // default: 1 x cup (16oz or 22oz) and 1 x regular straw
            if ($use22 && $cup22) {
                $sync[$cup22->id] = ['quantity' => 1];
            } elseif ($cup16) {
                $sync[$cup16->id] = ['quantity' => 1];
            }

            if ($strawRegular) $sync[$strawRegular->id] = ['quantity' => 1];

            // for products likely to be boba/pearls, use jumbo straw if available
            if (Str::contains($lower, ['boba', 'pearl', 'pearls', 'tapioca']) && $strawJumbo) {
                // prefer jumbo straw for boba-like products
                if (isset($sync[$strawRegular->id])) unset($sync[$strawRegular->id]);
                $sync[$strawJumbo->id] = ['quantity' => 1];
            }

            if (!empty($sync)) {
                $product->supplies()->syncWithoutDetaching($sync);
            }
        }
    }
}
