<?php

use App\Models\Category;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('categories', function (Blueprint $table) {
            $table->id();
            $table->string('name')->unique();
            $table->string('slug')->unique();
            $table->text('description')->nullable();
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });

        foreach ([
            ['name' => 'Milk Tea', 'slug' => 'milk_tea'],
            ['name' => 'Topping', 'slug' => 'topping'],
            ['name' => 'Supply', 'slug' => 'supply'],
        ] as $category) {
            Category::create($category);
        }

        if (DB::getDriverName() === 'mysql') {
            DB::statement('ALTER TABLE products MODIFY category VARCHAR(100) NOT NULL');
        }

        Schema::table('products', function (Blueprint $table) {
            $table->foreignId('category_id')->nullable()->after('category')->constrained()->nullOnDelete();
            $table->enum('item_type', ['sellable', 'inventory'])->default('sellable')->after('category_id');
            $table->unsignedInteger('low_stock_threshold')->default(10)->after('stock');
        });

        DB::table('products')->orderBy('id')->each(function ($product) {
            $category = DB::table('categories')->where('slug', $product->category)->first();

            if ($category) {
                DB::table('products')->where('id', $product->id)->update([
                    'category_id' => $category->id,
                ]);
            }
        });
    }

    public function down(): void
    {
        Schema::table('products', function (Blueprint $table) {
            $table->dropConstrainedForeignId('category_id');
            $table->dropColumn(['item_type', 'low_stock_threshold']);
        });

        Schema::dropIfExists('categories');
    }
};
