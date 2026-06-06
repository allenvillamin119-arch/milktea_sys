<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;
use App\Models\User;
use App\Models\Product;

class PosSupplyConsumptionTest extends TestCase
{
    use RefreshDatabase;

    public function test_sale_consumes_supplies()
    {
        // seed admin/cashier
        $cashier = User::factory()->create(['role' => 'cashier']);

        // create supply and product
        $cup = Product::create([ 'name' => 'Plastic Cup (16oz)', 'category' => 'supply', 'price' => 1.0, 'stock' => 100, 'is_active' => true ]);
        $tea = Product::create([ 'name' => 'Test Milk Tea', 'category' => 'milk_tea', 'price' => 50.0, 'stock' => 10, 'item_type' => 'sellable', 'is_active' => true ]);

        // map supply -> 1 per product
        $tea->supplies()->attach($cup->id, ['quantity' => 1]);

        $payload = [
            'items' => [ ['productId' => $tea->id, 'quantity' => 2] ],
            'cash_received' => 200,
            'payment_method' => 'cash',
        ];

        $response = $this->actingAs($cashier)->postJson(route('pos.process'), $payload);

        $response->assertStatus(200)->assertJson(['success' => true]);

        $cup->refresh();
        $tea->refresh();

        $this->assertEquals(98, $cup->stock);
        $this->assertEquals(8, $tea->stock);
    }

    public function test_void_restores_supplies()
    {
        $cashier = User::factory()->create(['role' => 'cashier']);

        $cup = Product::create([ 'name' => 'Plastic Cup (16oz)', 'category' => 'supply', 'price' => 1.0, 'stock' => 100, 'is_active' => true ]);
        $tea = Product::create([ 'name' => 'Test Milk Tea', 'category' => 'milk_tea', 'price' => 50.0, 'stock' => 10, 'item_type' => 'sellable', 'is_active' => true ]);

        $tea->supplies()->attach($cup->id, ['quantity' => 1]);

        $payload = [
            'items' => [ ['productId' => $tea->id, 'quantity' => 1] ],
            'cash_received' => 100,
            'payment_method' => 'cash',
        ];

        $resp = $this->actingAs($cashier)->postJson(route('pos.process'), $payload);
        $resp->assertStatus(200)->assertJson(['success' => true]);

        $transaction = $resp->json('transaction');
        $this->assertNotEmpty($transaction['transaction_number']);

        // now void
        $voidResp = $this->actingAs($cashier)->postJson(route('pos.void'), [
            'transaction_number' => $transaction['transaction_number'],
            'void_reason' => 'test void',
        ]);

        $voidResp->assertStatus(200)->assertJson(['success' => true]);

        $cup->refresh();
        $tea->refresh();

        // stocks restored
        $this->assertEquals(100, $cup->stock);
        $this->assertEquals(10, $tea->stock);
    }
}
