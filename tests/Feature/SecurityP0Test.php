<?php

namespace Tests\Feature;

use App\Models\Customer;
use App\Models\Order;
use App\Models\Outlet;
use App\Models\PaymentGateway;
use App\Models\Shift;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class SecurityP0Test extends TestCase
{
    use RefreshDatabase;

    public function test_wa_api_token_is_hidden_from_shared_inertia_outlet_props(): void
    {
        Outlet::create([
            'id' => 1,
            'name' => 'Laundry Express',
            'phone' => '081234567890',
            'is_wa_enabled' => true,
            'wa_api_token' => 'secret-super-private-token-12345',
        ]);

        $response = $this->get('/login');

        $response->assertStatus(200);
        $response->assertDontSee('secret-super-private-token-12345');

        $outletModel = Outlet::first();
        $this->assertArrayNotHasKey('wa_api_token', $outletModel->toArray());
    }

    public function test_server_key_is_hidden_from_payment_gateway_serialization(): void
    {
        PaymentGateway::create([
            'name' => 'midtrans',
            'display_name' => 'Midtrans',
            'is_active' => true,
            'mode' => 'sandbox',
            'server_key' => 'SB-Mid-server-SECRETKEY999',
            'client_key' => 'SB-Mid-client-PUBLICKEY111',
        ]);

        $gateway = PaymentGateway::first();
        $this->assertArrayNotHasKey('server_key', $gateway->toArray());

        $response = $this->getJson('/api/payment/active-gateway');
        $response->assertStatus(200);
        $response->assertDontSee('SB-Mid-server-SECRETKEY999');
    }

    public function test_order_payment_with_deposit_validates_and_deducts_customer_balance(): void
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

        $customer = Customer::create([
            'name' => 'Budi Santoso',
            'phone' => '081299998888',
            'deposit_balance' => 50000,
        ]);

        $order = Order::create([
            'invoice_code' => 'INV-TEST-0001',
            'customer_id' => $customer->id,
            'user_id' => $cashier->id,
            'order_date' => now()->toDateString(),
            'grand_total' => 100000,
            'paid_amount' => 0,
            'payment_status' => 'unpaid',
            'payment_method' => 'cash',
            'order_status' => 'received',
        ]);

        // 1. Gagal jika saldo deposit tidak mencukupi (misal bayar 60.000, saldo hanya 50.000)
        $responseFail = $this->actingAs($cashier)->post("/orders/{$order->id}/pay", [
            'amount' => 60000,
            'payment_method' => 'deposit',
        ]);
        $responseFail->assertSessionHas('error');
        $this->assertEquals(50000, $customer->fresh()->deposit_balance);

        // 2. Berhasil jika saldo cukup (bayar 30.000)
        $responseSuccess = $this->actingAs($cashier)->post("/orders/{$order->id}/pay", [
            'amount' => 30000,
            'payment_method' => 'deposit',
        ]);
        $responseSuccess->assertSessionHas('success');

        // Saldo berkurang menjadi 20.000
        $this->assertEquals(20000, $customer->fresh()->deposit_balance);
        $this->assertEquals(30000, $order->fresh()->paid_amount);
        $this->assertEquals('partial', $order->fresh()->payment_status);

        // Tercatat di mutasi deposit
        $this->assertDatabaseHas('customer_deposits', [
            'customer_id' => $customer->id,
            'amount' => 30000,
            'type' => 'order_deduction',
            'balance_after' => 20000,
        ]);
    }

    public function test_snap_token_locks_amount_to_remaining_balance(): void
    {
        PaymentGateway::create([
            'name' => 'midtrans',
            'display_name' => 'Midtrans',
            'is_active' => true,
            'mode' => 'sandbox',
            'server_key' => 'SB-Mid-server-DEMO',
            'client_key' => 'SB-Mid-client-DEMO',
        ]);

        $customer = Customer::create([
            'name' => 'Siti Rahma',
            'phone' => '081277776666',
        ]);

        $order = Order::create([
            'invoice_code' => 'INV-TEST-0002',
            'customer_id' => $customer->id,
            'order_date' => now()->toDateString(),
            'grand_total' => 100000,
            'paid_amount' => 100000,
            'payment_status' => 'paid',
            'payment_method' => 'cash',
            'order_status' => 'ready',
        ]);

        // Order yang sudah lunas harus ditolak
        $response = $this->postJson('/api/payment/snap-token', [
            'order_id' => $order->id,
        ]);
        $response->assertStatus(422);
        $response->assertJsonFragment([
            'status' => 'error',
            'message' => 'Tagihan order ini sudah lunas.',
        ]);
    }
}
