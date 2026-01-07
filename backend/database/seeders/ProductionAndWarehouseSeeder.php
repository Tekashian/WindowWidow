<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\ProductionOrder;
use App\Models\ProductionOrderItem;
use App\Models\ProductionBatch;
use App\Models\WarehouseDelivery;
use App\Models\Window;
use App\Models\User;
use Carbon\Carbon;

class ProductionAndWarehouseSeeder extends Seeder
{
    public function run(): void
    {
        // Get users
        $adminUser = User::where('email', 'admin@windowwidow.pl')->first();
        $productionUser = User::where('email', 'produkcja@windowwidow.pl')->first();
        $warehouseUser = User::where('email', 'magazyn@windowwidow.pl')->first();

        if (!$adminUser || !$productionUser || !$warehouseUser) {
            $this->command->error('Users not found. Please run UsersTableSeeder first.');
            return;
        }

        // Get some windows
        $windows = Window::limit(3)->get();
        if ($windows->isEmpty()) {
            $this->command->error('No windows found. Please run WindowsTableSeeder first.');
            return;
        }

        $this->command->info('Creating production orders and deliveries...');

        // 1. Completed order with delivered delivery
        $order1 = ProductionOrder::create([
            'order_number' => 'PRD-2026-001',
            'source_type' => 'stock_replenishment',
            'source_id' => null,
            'customer_name' => 'WindowWidow Magazyn',
            'delivery_address' => 'ul. Magazynowa 10',
            'delivery_city' => 'Warszawa',
            'delivery_postal_code' => '01-234',
            'priority' => 'normal',
            'status' => 'completed',
            'notes' => 'Uzupełnienie magazynu - okna standardowe',
            'created_by' => $adminUser->id,
            'started_at' => Carbon::now()->subDays(10),
            'completed_at' => Carbon::now()->subDays(3),
            'estimated_completion_at' => Carbon::now()->subDays(3),
        ]);

        ProductionOrderItem::create([
            'production_order_id' => $order1->id,
            'window_id' => $windows[0]->id,
            'quantity' => 10,
        ]);

        $batch1 = ProductionBatch::create([
            'production_order_id' => $order1->id,
            'batch_number' => 'BATCH-2026-001',
            'quantity' => 10,
            'status' => 'shipped',
            'quality_check_passed' => true,
            'started_at' => Carbon::now()->subDays(10),
            'completed_at' => Carbon::now()->subDays(3),
        ]);

        WarehouseDelivery::create([
            'production_order_id' => $order1->id,
            'batch_id' => $batch1->id,
            'delivery_number' => 'DEL-2026-0001',
            'expected_delivery_date' => Carbon::now()->subDays(2),
            'actual_delivery_date' => Carbon::now()->subDays(2),
            'status' => 'delivered',
            'items' => [
                [
                    'window_id' => $windows[0]->id,
                    'quantity' => 10,
                    'condition' => 'good'
                ]
            ],
            'shipped_by' => $productionUser->id,
            'received_by' => $warehouseUser->id,
            'shipped_at' => Carbon::now()->subDays(2)->setHour(8),
            'received_at' => Carbon::now()->subDays(2)->setHour(14),
            'notes' => 'Dostawa w dobrym stanie, wszystkie okna sprawdzone',
        ]);

        // 2. Order in production with pending delivery
        $order2 = ProductionOrder::create([
            'order_number' => 'PRD-2026-002',
            'source_type' => 'stock_replenishment',
            'source_id' => null,
            'customer_name' => 'WindowWidow Magazyn',
            'delivery_address' => 'ul. Magazynowa 10',
            'delivery_city' => 'Warszawa',
            'delivery_postal_code' => '01-234',
            'priority' => 'high',
            'status' => 'in_progress',
            'notes' => 'Pilne uzupełnienie magazynu - sezon wiosenny',
            'created_by' => $adminUser->id,
            'started_at' => Carbon::now()->subDays(3),
            'estimated_completion_at' => Carbon::now()->addDays(2),
        ]);

        ProductionOrderItem::create([
            'production_order_id' => $order2->id,
            'window_id' => $windows[1]->id,
            'quantity' => 15,
        ]);

        $batch2 = ProductionBatch::create([
            'production_order_id' => $order2->id,
            'batch_number' => 'BATCH-2026-002',
            'quantity' => 15,
            'status' => 'in_production',
            'started_at' => Carbon::now()->subDays(3),
        ]);

        WarehouseDelivery::create([
            'production_order_id' => $order2->id,
            'batch_id' => $batch2->id,
            'delivery_number' => 'DEL-2026-0002',
            'expected_delivery_date' => Carbon::now()->addDays(2),
            'status' => 'pending',
            'items' => [
                [
                    'window_id' => $windows[1]->id,
                    'quantity' => 15,
                    'condition' => 'pending'
                ]
            ],
            'notes' => 'Oczekuje na zakończenie produkcji',
        ]);

        // 3. Order ready to ship - in_transit
        $order3 = ProductionOrder::create([
            'order_number' => 'PRD-2026-003',
            'source_type' => 'stock_replenishment',
            'source_id' => null,
            'customer_name' => 'WindowWidow Magazyn',
            'delivery_address' => 'ul. Magazynowa 10',
            'delivery_city' => 'Warszawa',
            'delivery_postal_code' => '01-234',
            'priority' => 'normal',
            'status' => 'completed',
            'notes' => 'Okna premium - ostrożnie transportować',
            'created_by' => $adminUser->id,
            'started_at' => Carbon::now()->subDays(5),
            'completed_at' => Carbon::now()->subHours(4),
            'estimated_completion_at' => Carbon::now(),
        ]);

        ProductionOrderItem::create([
            'production_order_id' => $order3->id,
            'window_id' => $windows[2]->id,
            'quantity' => 8,
        ]);

        $batch3 = ProductionBatch::create([
            'production_order_id' => $order3->id,
            'batch_number' => 'BATCH-2026-003',
            'quantity' => 8,
            'status' => 'shipped',
            'quality_check_passed' => true,
            'started_at' => Carbon::now()->subDays(5),
            'completed_at' => Carbon::now()->subHours(4),
        ]);

        WarehouseDelivery::create([
            'production_order_id' => $order3->id,
            'batch_id' => $batch3->id,
            'delivery_number' => 'DEL-2026-0003',
            'expected_delivery_date' => Carbon::now(),
            'status' => 'in_transit',
            'items' => [
                [
                    'window_id' => $windows[2]->id,
                    'quantity' => 8,
                    'condition' => 'good'
                ]
            ],
            'shipped_by' => $productionUser->id,
            'shipped_at' => Carbon::now()->subHours(2),
            'notes' => 'W drodze do magazynu, szacowany czas dostawy: 2h',
        ]);

        // 4. Delayed delivery - opóźniona
        $order4 = ProductionOrder::create([
            'order_number' => 'PRD-2026-004',
            'source_type' => 'stock_replenishment',
            'source_id' => null,
            'customer_name' => 'WindowWidow Magazyn',
            'delivery_address' => 'ul. Magazynowa 10',
            'delivery_city' => 'Warszawa',
            'delivery_postal_code' => '01-234',
            'priority' => 'urgent',
            'status' => 'in_progress',
            'notes' => 'PILNE - opóźnienie w produkcji',
            'created_by' => $adminUser->id,
            'started_at' => Carbon::now()->subDays(7),
            'estimated_completion_at' => Carbon::now()->subDays(1),
        ]);

        ProductionOrderItem::create([
            'production_order_id' => $order4->id,
            'window_id' => $windows[0]->id,
            'quantity' => 20,
        ]);

        $batch4 = ProductionBatch::create([
            'production_order_id' => $order4->id,
            'batch_number' => 'BATCH-2026-004',
            'quantity' => 20,
            'status' => 'in_production',
            'started_at' => Carbon::now()->subDays(7),
        ]);

        WarehouseDelivery::create([
            'production_order_id' => $order4->id,
            'batch_id' => $batch4->id,
            'delivery_number' => 'DEL-2026-0004',
            'expected_delivery_date' => Carbon::now()->subDays(1),
            'status' => 'pending',
            'items' => [
                [
                    'window_id' => $windows[0]->id,
                    'quantity' => 20,
                    'condition' => 'pending'
                ]
            ],
            'notes' => 'OPÓŹNIENIE - problemy z materiałami',
        ]);

        // 5. Future delivery
        $order5 = ProductionOrder::create([
            'order_number' => 'PRD-2026-005',
            'source_type' => 'stock_replenishment',
            'source_id' => null,
            'customer_name' => 'WindowWidow Magazyn',
            'delivery_address' => 'ul. Magazynowa 10',
            'delivery_city' => 'Warszawa',
            'delivery_postal_code' => '01-234',
            'priority' => 'low',
            'status' => 'pending',
            'notes' => 'Planowane uzupełnienie na przyszły tydzień',
            'created_by' => $adminUser->id,
            'estimated_completion_at' => Carbon::now()->addDays(5),
        ]);

        ProductionOrderItem::create([
            'production_order_id' => $order5->id,
            'window_id' => $windows[1]->id,
            'quantity' => 12,
        ]);

        WarehouseDelivery::create([
            'production_order_id' => $order5->id,
            'batch_id' => null,
            'delivery_number' => 'DEL-2026-0005',
            'expected_delivery_date' => Carbon::now()->addDays(5),
            'status' => 'pending',
            'items' => [
                [
                    'window_id' => $windows[1]->id,
                    'quantity' => 12,
                    'condition' => 'pending'
                ]
            ],
            'notes' => 'Planowana dostawa za 5 dni',
        ]);

        // 6. Rejected delivery
        $order6 = ProductionOrder::create([
            'order_number' => 'PRD-2026-006',
            'source_type' => 'stock_replenishment',
            'source_id' => null,
            'customer_name' => 'WindowWidow Magazyn',
            'delivery_address' => 'ul. Magazynowa 10',
            'delivery_city' => 'Warszawa',
            'delivery_postal_code' => '01-234',
            'priority' => 'normal',
            'status' => 'completed',
            'notes' => 'Dostawa odrzucona z powodu uszkodzeń',
            'created_by' => $adminUser->id,
            'started_at' => Carbon::now()->subDays(8),
            'completed_at' => Carbon::now()->subDays(4),
            'estimated_completion_at' => Carbon::now()->subDays(4),
        ]);

        ProductionOrderItem::create([
            'production_order_id' => $order6->id,
            'window_id' => $windows[2]->id,
            'quantity' => 5,
        ]);

        $batch6 = ProductionBatch::create([
            'production_order_id' => $order6->id,
            'batch_number' => 'BATCH-2026-006',
            'quantity' => 5,
            'status' => 'rejected',
            'quality_check_passed' => false,
            'quality_notes' => 'Uszkodzenia mechaniczne wykryte podczas kontroli',
            'started_at' => Carbon::now()->subDays(8),
            'completed_at' => Carbon::now()->subDays(4),
        ]);

        WarehouseDelivery::create([
            'production_order_id' => $order6->id,
            'batch_id' => $batch6->id,
            'delivery_number' => 'DEL-2026-0006',
            'expected_delivery_date' => Carbon::now()->subDays(4),
            'status' => 'rejected',
            'items' => [
                [
                    'window_id' => $windows[2]->id,
                    'quantity' => 5,
                    'condition' => 'damaged'
                ]
            ],
            'rejection_reason' => 'Uszkodzenia mechaniczne w 3 oknach, rysy na szybach',
            'shipped_by' => $productionUser->id,
            'shipped_at' => Carbon::now()->subDays(4)->setHour(9),
            'notes' => 'Odrzucono - wymaga ponownej produkcji',
        ]);

        $this->command->info('✓ Created 6 production orders with warehouse deliveries');
        $this->command->info('  - 1 delivered');
        $this->command->info('  - 1 in transit');
        $this->command->info('  - 2 pending (1 delayed)');
        $this->command->info('  - 1 future delivery');
        $this->command->info('  - 1 rejected');
    }
}
