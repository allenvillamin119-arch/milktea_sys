<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // Step 1: Add new columns to products table for better categorization
        if (!Schema::hasColumn('products', 'type')) {
            Schema::table('products', function (Blueprint $table) {
                $table->enum('type', ['product', 'supply'])->default('product')->after('category_id');
            });
        }

        // Step 2: Update existing products based on their category
        DB::statement("UPDATE products SET type = CASE 
            WHEN category = 'supply' THEN 'supply'
            ELSE 'product'
        END");

        // Step 3: Update item_type for better clarity
        DB::statement("UPDATE products SET item_type = CASE 
            WHEN category = 'supply' THEN 'inventory'
            WHEN category = 'topping' THEN 'sellable'
            ELSE item_type
        END");

        // Step 4: Rename 'category' enum values for products to be more specific
        // First, add a temporary column
        if (!Schema::hasColumn('products', 'temp_category')) {
            Schema::table('products', function (Blueprint $table) {
                $table->string('temp_category')->nullable();
            });
        }

        // Copy and transform category data
        DB::statement("UPDATE products SET temp_category = CASE 
            WHEN category = 'milk_tea' THEN 'milktea'
            WHEN category = 'topping' THEN 'topping'
            WHEN category = 'supply' THEN 'supply'
            ELSE category
        END");

        // Step 5: Create a view for Products (sellable items only)
        // This helps separate the logic
        try {
            DB::statement("DROP VIEW IF EXISTS sellable_products");
            DB::statement("
                CREATE VIEW sellable_products AS
                SELECT * FROM products 
                WHERE type = 'product' AND is_active = 1
            ");
        } catch (\Exception $e) {
            // View creation might fail, continue
        }

        // Step 6: Create a view for Inventory (supplies only)
        try {
            DB::statement("DROP VIEW IF EXISTS inventory_supplies");
            DB::statement("
                CREATE VIEW inventory_supplies AS
                SELECT * FROM products 
                WHERE type = 'supply' AND is_active = 1
            ");
        } catch (\Exception $e) {
            // View creation might fail, continue
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Drop views
        try {
            DB::statement("DROP VIEW IF EXISTS sellable_products");
            DB::statement("DROP VIEW IF EXISTS inventory_supplies");
        } catch (\Exception $e) {
            // Continue
        }

        // Remove temporary column
        Schema::table('products', function (Blueprint $table) {
            $table->dropColumn('temp_category');
            $table->dropColumn('type');
        });

        // Reset to original state
        DB::statement("UPDATE products SET item_type = 'sellable' WHERE category != 'supply'");
        DB::statement("UPDATE products SET item_type = 'inventory' WHERE category = 'supply'");
    }
};