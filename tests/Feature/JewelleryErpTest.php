<?php

namespace Tests\Feature;

use App\Models\Branch;
use App\Models\Customer;
use App\Models\InventoryItem;
use App\Models\User;
use App\Services\JewelleryPriceCalculator;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class JewelleryErpTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed();
    }

    public function test_jewellery_price_calculator_matches_example_and_gst_rules(): void
    {
        $calc = new JewelleryPriceCalculator();

        // Example from todo.txt: 10g @ ₹10,000/g + ₹10,000 making + ₹5,000 stone = ₹1,15,000 + 3% GST = ₹1,18,450
        $res = $calc->calculateInvoice([[
            'gross_weight' => 10,
            'stone_weight' => 0,
            'metal_rate' => 10000,
            'making_charge_type' => 'fixed',
            'making_charge_rate' => 10000,
            'stone_charge' => 5000,
            'gst_rate' => 3,
        ]]);

        $this->assertEquals(115000.00, $res['subtotal']);
        $this->assertEquals(1725.00, $res['cgst_amount']);
        $this->assertEquals(1725.00, $res['sgst_amount']);
        $this->assertEquals(118450.00, $res['grand_total']);
    }

    public function test_old_gold_valuation_calculator(): void
    {
        $calc = new JewelleryPriceCalculator();
        $res = $calc->calculateOldGold([
            'gross_weight' => 10.5,
            'stone_weight' => 0.5,
            'purity_pct' => 91.6,
            'deduction_pct' => 2.0,
            'melting_loss_pct' => 1.0,
            'applied_rate' => 8000,
        ]);

        $this->assertEquals(10.0, $res['net_weight']);
        $this->assertEquals(9.16, $res['fine_gold_weight']);
        $this->assertGreaterThan(70000, $res['valuation_amount']);
    }

    public function test_all_modules_load_for_authenticated_admin(): void
    {
        $admin = User::where('email', 'admin@swarnaerp.in')->firstOrFail();
        $this->actingAs($admin);

        $routes = [
            '/',
            '/rates',
            '/products',
            '/inventory',
            '/inventory/labels',
            '/stones-hallmark',
            '/pos',
            '/invoices',
            '/customers',
            '/old-gold',
            '/purchases',
            '/workshop',
            '/schemes',
            '/finance',
            '/staff',
            '/branches',
            '/security',
            '/reports',
        ];

        foreach ($routes as $uri) {
            $response = $this->get($uri);
            $response->assertStatus(200, "Failed on route: {$uri}");
        }
    }

    public function test_pos_checkout_creates_invoice_and_marks_item_sold(): void
    {
        $admin = User::where('email', 'admin@swarnaerp.in')->firstOrFail();
        $this->actingAs($admin);

        $item = InventoryItem::with('product')->where('status', 'available')->firstOrFail();
        $customer = Customer::firstOrFail();
        $branch = Branch::firstOrFail();

        $response = $this->post(route('pos.store'), [
            'branch_id' => $branch->id,
            'customer_id' => $customer->id,
            'invoice_type' => 'sale',
            'gst_type' => 'intra',
            'payment_method' => 'upi',
            'paid_amount' => 100000,
            'items' => [[
                'inventory_item_id' => $item->id,
                'product_name' => $item->product->name,
                'metal_type' => $item->product->metal_type,
                'purity' => $item->purity,
                'gross_weight' => $item->gross_weight,
                'stone_weight' => $item->stone_weight,
                'net_weight' => $item->net_weight,
                'metal_rate' => 7200,
                'making_charge_type' => 'per_gram',
                'making_charge_rate' => 600,
                'wastage_pct' => 2,
                'stone_charge' => 0,
                'diamond_charge' => 0,
                'hallmark_charge' => 45,
                'other_charge' => 0,
                'discount' => 0,
                'gst_rate' => 3,
            ]],
        ]);

        $response->assertRedirect();
        $this->assertEquals('sold', $item->fresh()->status);
    }

    public function test_guests_cannot_access_any_protected_module(): void
    {
        $routes = [
            '/', '/rates', '/products', '/inventory', '/inventory/labels',
            '/stones-hallmark', '/pos', '/invoices', '/customers', '/old-gold',
            '/purchases', '/workshop', '/schemes', '/finance', '/staff',
            '/branches', '/security', '/reports',
        ];

        foreach ($routes as $uri) {
            $this->get($uri)->assertRedirect('/login');
        }

        $this->post('/pos/checkout', [])->assertRedirect('/login');
        $this->post('/quick-login', ['email' => 'admin@swarnaerp.in'])->assertStatus(404);
    }

    public function test_login_requires_valid_password_and_active_account(): void
    {
        $this->post('/login', [
            'email' => 'admin@swarnaerp.in',
            'password' => 'wrong-password',
        ])->assertSessionHasErrors('email');
        $this->assertGuest();

        $admin = User::where('email', 'admin@swarnaerp.in')->firstOrFail();
        $admin->update(['is_active' => false]);

        $this->post('/login', [
            'email' => 'admin@swarnaerp.in',
            'password' => 'password',
        ])->assertSessionHasErrors('email');
        $this->assertGuest();

        $admin->update(['is_active' => true]);

        $this->post('/login', [
            'email' => 'admin@swarnaerp.in',
            'password' => 'password',
        ])->assertRedirect('/');
        $this->assertAuthenticatedAs($admin);
    }

    public function test_rates_sync_endpoint(): void
    {
        $admin = User::where('email', 'admin@swarnaerp.in')->firstOrFail();
        $this->actingAs($admin);

        $response = $this->post(route('rates.sync'));
        $response->assertRedirect();
    }

    public function test_rate_deletion(): void
    {
        $admin = User::where('email', 'admin@swarnaerp.in')->firstOrFail();
        $this->actingAs($admin);

        $rate = \App\Models\MetalRate::firstOrFail();
        $rateId = $rate->id;

        $response = $this->delete(route('rates.destroy', $rate));
        $response->assertRedirect();
        $this->assertDatabaseMissing('metal_rates', ['id' => $rateId]);
    }
}

