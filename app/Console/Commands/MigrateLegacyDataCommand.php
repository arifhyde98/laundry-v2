<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use App\Models\User;
use App\Models\Outlet;
use App\Models\Rack;
use App\Models\Service;
use App\Models\Customer;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\OrderPayment;
use App\Models\OrderTrackingLog;
use App\Models\InventoryItem;
use App\Models\ChemicalRecipe;

class MigrateLegacyDataCommand extends Command
{
    protected $signature = 'migrate:legacy-laundry';
    protected $description = 'Migrate data from legacy laundry MySQL database to laundry_v2';

    public function handle()
    {
        $this->info('Starting ETL data migration from legacy laundry database...');

        // Setup temporary PDO connection to legacy database
        try {
            $legacyPdo = new \PDO("mysql:host=global-mysql;dbname=laundry;charset=utf8mb4", "root", "admin123", [
                \PDO::ATTR_ERRMODE => \PDO::ERRMODE_EXCEPTION,
                \PDO::ATTR_DEFAULT_FETCH_MODE => \PDO::FETCH_ASSOC,
            ]);
        } catch (\Exception $e) {
            $this->error('Failed to connect to legacy database: ' . $e->getMessage());
            return 1;
        }

        DB::beginTransaction();

        try {
            // 1. Create Default Outlet
            $outlet = Outlet::firstOrCreate(
                ['name' => 'Outlet Pusat'],
                [
                    'type' => 'store_and_workshop',
                    'phone' => '081234567890',
                    'address' => 'Jl. Utama Laundry No. 1',
                    'is_active' => true,
                ]
            );
            $this->info('✓ Outlet created.');

            // 2. Create Default Racks
            $racks = ['RAK-A1', 'RAK-A2', 'RAK-A3', 'RAK-B1', 'RAK-B2', 'GANTUNG-01', 'GANTUNG-02'];
            foreach ($racks as $code) {
                Rack::firstOrCreate(
                    ['rack_code' => $code],
                    [
                        'outlet_id' => $outlet->id,
                        'category' => str_starts_with($code, 'GANTUNG') ? 'hanger' : 'regular',
                        'capacity' => 10,
                        'is_available' => true,
                    ]
                );
            }
            $this->info('✓ Default storage racks created.');

            // 3. Migrate Admin Users
            $admins = $legacyPdo->query("SELECT * FROM admin")->fetchAll();
            foreach ($admins as $adm) {
                User::updateOrCreate(
                    ['username' => $adm['username']],
                    [
                        'name' => ucfirst($adm['username']),
                        'email' => $adm['username'] . '@laundry.local',
                        'phone' => '081234567890',
                        'role' => 'owner',
                        'password' => Hash::make('123456'), // Default password
                        'is_active' => true,
                    ]
                );
            }

            // Also create a default Cashier and Washer user for testing RBAC
            User::firstOrCreate(
                ['username' => 'kasir'],
                [
                    'name' => 'Kasir 1',
                    'email' => 'kasir@laundry.local',
                    'phone' => '081234567891',
                    'role' => 'cashier',
                    'password' => Hash::make('123456'),
                    'is_active' => true,
                ]
            );
            User::firstOrCreate(
                ['username' => 'operator'],
                [
                    'name' => 'Operator Cuci',
                    'email' => 'operator@laundry.local',
                    'phone' => '081234567892',
                    'role' => 'washer',
                    'password' => Hash::make('123456'),
                    'is_active' => true,
                ]
            );
            $this->info('✓ Users & Roles created.');

            // 4. Migrate Services (Harga)
            $hargaRow = $legacyPdo->query("SELECT * FROM harga LIMIT 1")->fetch();
            $hargaPerKilo = $hargaRow ? (float)$hargaRow['harga_per_kilo'] : 6000;

            $serviceKiloan = Service::updateOrCreate(
                ['name' => 'Cuci Kiloan Reguler'],
                [
                    'outlet_id' => $outlet->id,
                    'unit' => 'kg',
                    'price' => $hargaPerKilo,
                    'estimated_hours' => 72,
                    'description' => 'Cuci kering setrika reguler 3 hari',
                    'is_active' => true,
                ]
            );

            // Add standard modern services as well
            Service::firstOrCreate(
                ['name' => 'Cuci Kiloan Express (1 Hari)'],
                [
                    'outlet_id' => $outlet->id,
                    'unit' => 'kg',
                    'price' => $hargaPerKilo * 1.5,
                    'estimated_hours' => 24,
                    'description' => 'Cuci kering setrika kilat 1 hari',
                    'is_active' => true,
                ]
            );
            Service::firstOrCreate(
                ['name' => 'Cuci Bedcover'],
                [
                    'outlet_id' => $outlet->id,
                    'unit' => 'pcs',
                    'price' => 25000,
                    'estimated_hours' => 48,
                    'description' => 'Cuci satuan bedcover besar/kecil',
                    'is_active' => true,
                ]
            );
            Service::firstOrCreate(
                ['name' => 'Cuci Sepatu Premium'],
                [
                    'outlet_id' => $outlet->id,
                    'unit' => 'pasang',
                    'price' => 30000,
                    'estimated_hours' => 48,
                    'description' => 'Deep cleaning & unyellowing sepatu',
                    'is_active' => true,
                ]
            );
            $this->info('✓ Services & Pricing created.');

            // 5. Create Default Inventory & Chemical Recipes
            $detergent = InventoryItem::firstOrCreate(
                ['name' => 'Deterjen Cair Premium'],
                ['category' => 'chemical', 'stock' => 50000, 'unit' => 'ml', 'minimum_stock' => 5000, 'cost_price' => 25]
            );
            $perfume = InventoryItem::firstOrCreate(
                ['name' => 'Parfum Laundry Lavender'],
                ['category' => 'chemical', 'stock' => 20000, 'unit' => 'ml', 'minimum_stock' => 2000, 'cost_price' => 40]
            );
            InventoryItem::firstOrCreate(
                ['name' => 'Plastik Packing 35x50'],
                ['category' => 'packaging', 'stock' => 500, 'unit' => 'pcs', 'minimum_stock' => 50, 'cost_price' => 300]
            );

            ChemicalRecipe::firstOrCreate(
                ['inventory_item_id' => $detergent->id],
                ['dosage_per_kg' => 25]
            );
            ChemicalRecipe::firstOrCreate(
                ['inventory_item_id' => $perfume->id],
                ['dosage_per_kg' => 15]
            );
            $this->info('✓ Inventory & Auto Chemical recipes created.');

            // 6. Migrate Customers
            $pelangganList = $legacyPdo->query("SELECT * FROM pelanggan")->fetchAll();
            $customerMap = []; // old_id => new_id

            foreach ($pelangganList as $pel) {
                $cust = Customer::updateOrCreate(
                    ['id' => $pel['pelanggan_id']],
                    [
                        'name' => $pel['pelanggan_nama'],
                        'phone' => $pel['pelanggan_hp'],
                        'address' => $pel['pelanggan_alamat'],
                        'deposit_balance' => 0,
                        'point_balance' => 0,
                    ]
                );
                $customerMap[$pel['pelanggan_id']] = $cust->id;
            }
            $this->info('✓ ' . count($pelangganList) . ' Customers migrated.');

            // 7. Migrate Transactions & Clothes
            $transaksiList = $legacyPdo->query("SELECT * FROM transaksi")->fetchAll();
            $owner = User::first();

            foreach ($transaksiList as $trx) {
                $statusMap = [
                    '0' => 'received',
                    '1' => 'washing',
                    '2' => 'completed',
                ];
                $orderStatus = $statusMap[$trx['transaksi_status']] ?? 'received';
                $invoiceCode = 'INV-LGD-' . str_pad($trx['transaksi_id'], 4, '0', STR_PAD_LEFT);

                $order = Order::updateOrCreate(
                    ['id' => $trx['transaksi_id']],
                    [
                        'invoice_code' => $invoiceCode,
                        'outlet_id' => $outlet->id,
                        'customer_id' => $customerMap[$trx['transaksi_pelanggan']] ?? Customer::first()->id,
                        'user_id' => $owner->id,
                        'rack_id' => $orderStatus === 'completed' ? null : 1,
                        'total_weight_qty' => (float)$trx['transaksi_berat'],
                        'subtotal_amount' => (float)$trx['transaksi_harga'],
                        'discount_amount' => 0,
                        'delivery_fee' => 0,
                        'grand_total' => (float)$trx['transaksi_harga'],
                        'paid_amount' => (float)$trx['transaksi_harga'],
                        'payment_status' => 'paid',
                        'payment_method' => 'cash',
                        'order_status' => $orderStatus,
                        'order_date' => $trx['transaksi_tgl'],
                        'estimated_completion' => $trx['transaksi_tgl_selesai'],
                        'actual_completion' => $orderStatus === 'completed' ? $trx['transaksi_tgl_selesai'] : null,
                    ]
                );

                // Create Order Payment Record
                OrderPayment::firstOrCreate(
                    ['order_id' => $order->id],
                    [
                        'amount_paid' => (float)$trx['transaksi_harga'],
                        'payment_method' => 'cash',
                        'received_by' => $owner->id,
                        'paid_at' => $trx['transaksi_tgl'],
                    ]
                );

                // Create Tracking Log
                OrderTrackingLog::firstOrCreate(
                    ['order_id' => $order->id, 'status_to' => $orderStatus],
                    [
                        'changed_by' => $owner->id,
                        'status_from' => 'received',
                        'notes' => 'Migrated from legacy transaction ID: ' . $trx['transaksi_id'],
                    ]
                );
            }
            $this->info('✓ ' . count($transaksiList) . ' Orders & Payments migrated.');

            // 8. Migrate Pakaian items
            $pakaianList = $legacyPdo->query("SELECT * FROM pakaian")->fetchAll();
            foreach ($pakaianList as $pak) {
                OrderItem::firstOrCreate(
                    ['id' => $pak['pakaian_id']],
                    [
                        'order_id' => $pak['pakaian_transaksi'],
                        'service_id' => $serviceKiloan->id,
                        'item_name' => $pak['pakaian_jenis'],
                        'quantity' => (float)$pak['pakaian_jumlah'],
                        'unit_price' => 0,
                        'subtotal' => 0,
                    ]
                );
            }
            $this->info('✓ ' . count($pakaianList) . ' Clothing items migrated.');

            DB::commit();
            $this->info('🎉 Data migration completed successfully without errors!');
            return 0;
        } catch (\Exception $e) {
            DB::rollBack();
            $this->error('Migration error: ' . $e->getMessage());
            return 1;
        }
    }
}

