<?php

namespace Tests\Feature;

use App\Models\Customer;
use App\Models\Order;
use App\Models\Outlet;
use App\Models\PaymentGateway;
use App\Models\Service;
use App\Models\Shift;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class RegressionTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        Outlet::create([
            'id' => 1,
            'name' => 'Laundry Express',
            'phone' => '081234567890',
            'address' => 'Jl. Laundry No. 1',
            'receipt_header' => 'Bersih & Rapi',
            'receipt_footer' => 'Terima Kasih',
            'receipt_paper_size' => '58mm',
            'is_wa_enabled' => true,
            'wa_api_token' => 'test-token-fonnte',
            'commission_washing' => 500,
            'commission_ironing' => 1000,
            'commission_packing' => 200,
        ]);
    }

    public function test_owner_can_view_and_update_outlet_with_wa_token(): void
    {
        $owner = User::factory()->create([
            'role' => 'owner',
            'is_active' => true,
        ]);

        // 1. Owner can view outlet page and wa_api_token is present in page props
        $response = $this->actingAs($owner)->get('/outlet');
        $response->assertStatus(200);
        $response->assertSee('test-token-fonnte');

        // 2. Owner can update outlet and change wa_api_token
        $updateResponse = $this->actingAs($owner)->put('/outlet', [
            'name' => 'Laundry Express Reborn',
            'phone' => '081299990000',
            'address' => 'Jl. Baru No. 2',
            'receipt_header' => 'Wangi & Segar',
            'receipt_footer' => 'Sampai Jumpa Kembali',
            'receipt_paper_size' => '80mm',
            'is_wa_enabled' => true,
            'wa_api_token' => 'updated-token-123',
            'commission_washing' => 600,
            'commission_ironing' => 1200,
            'commission_packing' => 250,
        ]);

        $updateResponse->assertSessionHas('success');
        $outlet = Outlet::find(1);
        $this->assertEquals('Laundry Express Reborn', $outlet->name);
        $this->assertEquals('updated-token-123', $outlet->wa_api_token);
        $this->assertEquals('80mm', $outlet->receipt_paper_size);
    }

    public function test_owner_can_view_and_update_payment_gateway_with_server_key(): void
    {
        $owner = User::factory()->create([
            'role' => 'owner',
            'is_active' => true,
        ]);

        $gateway = PaymentGateway::create([
            'name' => 'midtrans',
            'display_name' => 'Midtrans Payment Gateway',
            'is_active' => true,
            'mode' => 'sandbox',
            'server_key' => 'SB-Mid-server-INITIAL',
            'client_key' => 'SB-Mid-client-INITIAL',
        ]);

        // 1. Owner can view payment gateways page and see server_key
        $response = $this->actingAs($owner)->get('/payment-gateways');
        $response->assertStatus(200);
        $response->assertSee('SB-Mid-server-INITIAL');

        // 2. Owner can update server_key
        $updateResponse = $this->actingAs($owner)->put("/payment-gateways/{$gateway->id}", [
            'is_active' => true,
            'mode' => 'production',
            'server_key' => 'Mid-server-PROD-999',
            'client_key' => 'Mid-client-PROD-999',
        ]);

        $updateResponse->assertSessionHas('success');
        $this->assertEquals('Mid-server-PROD-999', $gateway->fresh()->server_key);
        $this->assertEquals('production', $gateway->fresh()->mode);
    }

    public function test_cash_payment_in_order_controller_increments_shift_cash(): void
    {
        $cashier = User::factory()->create([
            'role' => 'cashier',
            'is_active' => true,
        ]);

        $shift = Shift::create([
            'user_id' => $cashier->id,
            'starting_cash' => 50000,
            'expected_cash' => 50000,
            'cash_income' => 0,
            'non_cash_income' => 0,
            'status' => 'open',
            'opened_at' => now(),
        ]);

        $customer = Customer::create([
            'name' => 'Rini Astuti',
            'phone' => '081233334444',
            'deposit_balance' => 0,
        ]);

        $order = Order::create([
            'invoice_code' => 'INV-REGRESS-01',
            'customer_id' => $customer->id,
            'order_date' => now()->toDateString(),
            'grand_total' => 75000,
            'paid_amount' => 0,
            'payment_status' => 'unpaid',
            'payment_method' => 'cash',
            'order_status' => 'received',
        ]);

        // Bayar tunai Rp 75.000 (Lunas)
        $response = $this->actingAs($cashier)->post("/orders/{$order->id}/pay", [
            'amount' => 75000,
            'payment_method' => 'cash',
        ]);

        $response->assertSessionHas('success');
        $this->assertEquals(75000, $order->fresh()->paid_amount);
        $this->assertEquals('paid', $order->fresh()->payment_status);

        // Shift kasir bertambah
        $shift->refresh();
        $this->assertEquals(75000, $shift->cash_income);
        $this->assertEquals(125000, $shift->expected_cash);
        $this->assertEquals(0, $shift->non_cash_income);
    }

    public function test_qris_and_transfer_payment_increments_non_cash_income(): void
    {
        $cashier = User::factory()->create([
            'role' => 'cashier',
            'is_active' => true,
        ]);

        $shift = Shift::create([
            'user_id' => $cashier->id,
            'starting_cash' => 50000,
            'expected_cash' => 50000,
            'cash_income' => 0,
            'non_cash_income' => 0,
            'status' => 'open',
            'opened_at' => now(),
        ]);

        $customer = Customer::create([
            'name' => 'Agus Priyono',
            'phone' => '081255556666',
        ]);

        $order = Order::create([
            'invoice_code' => 'INV-REGRESS-02',
            'customer_id' => $customer->id,
            'order_date' => now()->toDateString(),
            'grand_total' => 60000,
            'paid_amount' => 0,
            'payment_status' => 'unpaid',
            'payment_method' => 'qris',
            'order_status' => 'received',
        ]);

        $response = $this->actingAs($cashier)->post("/orders/{$order->id}/pay", [
            'amount' => 60000,
            'payment_method' => 'qris',
        ]);

        $response->assertSessionHas('success');
        $this->assertEquals(60000, $order->fresh()->paid_amount);
        $this->assertEquals('paid', $order->fresh()->payment_status);

        $shift->refresh();
        $this->assertEquals(0, $shift->cash_income);
        $this->assertEquals(50000, $shift->expected_cash);
        $this->assertEquals(60000, $shift->non_cash_income);
    }

    public function test_public_tracking_page_renders_order_without_exposing_sensitive_tokens(): void
    {
        $customer = Customer::create([
            'name' => 'Dewi Lestari',
            'phone' => '081288889999',
        ]);

        $order = Order::create([
            'invoice_code' => 'INV-TRACK-001',
            'customer_id' => $customer->id,
            'order_date' => now()->toDateString(),
            'grand_total' => 45000,
            'paid_amount' => 0,
            'payment_status' => 'unpaid',
            'payment_method' => 'cash',
            'order_status' => 'washing',
        ]);

        $response = $this->get('/track/INV-TRACK-001');
        $response->assertStatus(200);
        $response->assertSee('Dewi Lestari');
        $response->assertSee('INV-TRACK-001');

        // Token rahasia WA maupun Server Key tidak boleh muncul di response HTML/JSON
        $response->assertDontSee('test-token-fonnte');
        $response->assertDontSee('SB-Mid-server');
    }

    public function test_pos_store_with_deposit_payment_still_works(): void
    {
        $cashier = User::factory()->create([
            'role' => 'cashier',
            'is_active' => true,
        ]);

        Shift::create([
            'user_id' => $cashier->id,
            'starting_cash' => 100000,
            'expected_cash' => 100000,
            'status' => 'open',
            'opened_at' => now(),
        ]);

        $service = Service::create([
            'name' => 'Cuci Komplit Reguler',
            'service_type' => 'kiloan',
            'unit' => 'kg',
            'price' => 10000,
            'estimated_hours' => 48,
            'is_active' => true,
        ]);

        $customer = Customer::create([
            'name' => 'Eko Prasetyo',
            'phone' => '081211112222',
            'deposit_balance' => 100000,
        ]);

        $response = $this->actingAs($cashier)->post('/pos', [
            'customer_id' => $customer->id,
            'items' => [
                [
                    'service_id' => $service->id,
                    'item_name' => 'Cuci Komplit Reguler',
                    'quantity' => 3,
                    'unit_price' => 10000,
                ],
            ],
            'discount_amount' => 0,
            'delivery_fee' => 0,
            'payment_status' => 'paid',
            'payment_method' => 'deposit',
            'paid_amount' => 30000,
        ]);

        $response->assertSessionHas('success');

        // Saldo customer berkurang dari 100.000 menjadi 70.000
        $this->assertEquals(70000, $customer->fresh()->deposit_balance);
        $this->assertDatabaseHas('orders', [
            'customer_id' => $customer->id,
            'grand_total' => 30000,
            'paid_amount' => 30000,
            'payment_status' => 'paid',
        ]);
    }
}
