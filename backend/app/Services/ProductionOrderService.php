<?php

namespace App\Services;

use App\Models\ProductionOrder;
use App\Models\ProductionOrderItem;
use App\Models\Material;
use Illuminate\Support\Facades\DB;
use Exception;

class ProductionOrderService
{
    /**
     * Utworzenie zlecenia produkcyjnego z walidacją stanów magazynowych
     */
    public function createProductionOrder(array $data): ProductionOrder
    {
        DB::beginTransaction();

        try {
            // Walidacja dostępności materiałów
            $this->validateMaterialAvailability($data['items']);

            // Utworzenie zlecenia
            $productionOrder = ProductionOrder::create([
                'order_number' => $this->generateOrderNumber(),
                'status' => 'nowe',
                'priority' => $data['priority'] ?? 'normalna',
                'notes' => $data['notes'] ?? null,
                'assigned_to' => $data['assigned_to'] ?? null,
            ]);

            // Dodanie pozycji
            foreach ($data['items'] as $item) {
                ProductionOrderItem::create([
                    'production_order_id' => $productionOrder->id,
                    'window_id' => $item['window_id'],
                    'quantity' => $item['quantity'],
                    'status' => 'oczekujace',
                ]);
            }

            DB::commit();
            return $productionOrder->load('items.window');

        } catch (Exception $e) {
            DB::rollBack();
            throw $e;
        }
    }

    /**
     * Rozpoczęcie produkcji - zużycie materiałów
     */
    public function startProduction(ProductionOrder $productionOrder): void
    {
        // Walidacja: sprawdź czy zlecenie może być rozpoczęte
        if (in_array($productionOrder->status, ['w_trakcie', 'zakonczone', 'anulowane'])) {
            throw new Exception("Nie można rozpocząć produkcji. Zlecenie ma już status: {$productionOrder->status}");
        }

        // Sprawdź czy zlecenie ma pozycje
        if ($productionOrder->items->isEmpty()) {
            throw new Exception("Nie można rozpocząć produkcji. Zlecenie nie zawiera żadnych pozycji produktowych.");
        }

        DB::beginTransaction();

        try {
            // Próbuj zużyć materiały jeśli są dostępne
            foreach ($productionOrder->items as $item) {
                if ($item->window) {
                    try {
                        $this->consumeMaterialsForWindow($item->window, $item->quantity, $productionOrder);
                    } catch (Exception $e) {
                        // Loguj błąd ale kontynuuj produkcję
                        \Log::warning('Material consumption failed', [
                            'order' => $productionOrder->order_number,
                            'error' => $e->getMessage()
                        ]);
                    }
                }
            }

            // Oblicz przewidywany czas produkcji
            $productionTimeHours = $this->calculateProductionTime($productionOrder);
            $estimatedWarehouseDeliveryDate = now()->addHours($productionTimeHours);

            // Zaktualizuj zlecenie
            $productionOrder->update([
                'production_time_hours' => $productionTimeHours,
                'estimated_warehouse_delivery_date' => $estimatedWarehouseDeliveryDate,
            ]);

            $productionOrder->start();

            // Wywołaj event powiadamiający magazyn
            event(new \App\Events\ProductionStarted($productionOrder));

            DB::commit();

        } catch (Exception $e) {
            DB::rollBack();
            throw $e;
        }
    }

    /**
     * Oblicz przewidywany czas produkcji w godzinach
     */
    private function calculateProductionTime(ProductionOrder $productionOrder): int
    {
        $totalTime = 0;
        
        foreach ($productionOrder->items as $item) {
            // Przykładowa logika: każde okno zajmuje 2 godziny produkcji
            $timePerWindow = 2;
            $totalTime += $timePerWindow * $item->quantity;
        }
        
        // Dodaj bufor czasowy w zależności od priorytetu
        $priorityMultiplier = match($productionOrder->priority) {
            'pilne' => 0.8,  // przyspieszona produkcja
            'wysoka' => 0.9,
            'normalna' => 1.0,
            'niska' => 1.2,
            default => 1.0,
        };
        
        return (int) ceil($totalTime * $priorityMultiplier);
    }

    /**
     * Zakończenie produkcji
     */
    public function completeProduction(ProductionOrder $productionOrder): void
    {
        $productionOrder->complete();
    }

    /**
     * Zużycie materiałów dla okna
     */
    private function consumeMaterialsForWindow($window, int $quantity, ProductionOrder $productionOrder): void
    {
        // Pomiń zużycie materiałów jeśli brak danych o oknie lub materiałach w bazie
        if (!$window || !$window->width || !$window->height) {
            return;
        }

        // Przykładowe zużycie materiałów (można rozbudować o bardziej złożoną logikę)
        
        // Profil - oblicz obwód okna w metrach
        $perimeter = (($window->width + $window->height) * 2) / 1000; // mm -> m
        $profileMaterial = Material::where('type', 'profil')->first();
        if ($profileMaterial && $profileMaterial->current_stock >= $perimeter * $quantity) {
            $profileMaterial->removeStock(
                $perimeter * $quantity, 
                "Zlecenie: {$productionOrder->order_number}"
            );
        }

        // Szyba - oblicz powierzchnię w m²
        $area = ($window->width * $window->height) / 1000000; // mm² -> m²
        $glassMaterial = Material::where('type', 'szyba')->first();
        if ($glassMaterial && $glassMaterial->current_stock >= $area * $quantity) {
            $glassMaterial->removeStock(
                $area * $quantity, 
                "Zlecenie: {$productionOrder->order_number}"
            );
        }

        // Okucia - stała ilość na okno
        $hardwareMaterial = Material::where('type', 'okucie')->first();
        if ($hardwareMaterial && $hardwareMaterial->current_stock >= $quantity) {
            $hardwareMaterial->removeStock(
                1 * $quantity, 
                "Zlecenie: {$productionOrder->order_number}"
            );
        }
    }

    /**
     * Walidacja dostępności materiałów przed utworzeniem zlecenia
     */
    private function validateMaterialAvailability(array $items): void
    {
        $profileMaterial = Material::where('type', 'profil')->first();
        $glassMaterial = Material::where('type', 'szyba')->first();
        $hardwareMaterial = Material::where('type', 'okucie')->first();

        foreach ($items as $item) {
            $window = \App\Models\Window::find($item['window_id']);
            $quantity = $item['quantity'];

            // Sprawdź profil
            $perimeter = (($window->width + $window->height) * 2) / 1000;
            if ($profileMaterial && ($perimeter * $quantity) > $profileMaterial->current_stock) {
                throw new Exception("Niewystarczająca ilość profili w magazynie");
            }

            // Sprawdź szybę
            $area = ($window->width * $window->height) / 1000000;
            if ($glassMaterial && ($area * $quantity) > $glassMaterial->current_stock) {
                throw new Exception("Niewystarczająca ilość szyb w magazynie");
            }

            // Sprawdź okucia
            if ($hardwareMaterial && $quantity > $hardwareMaterial->current_stock) {
                throw new Exception("Niewystarczająca ilość okuć w magazynie");
            }
        }
    }

    /**
     * Generowanie numeru zlecenia
     */
    private function generateOrderNumber(): string
    {
        return 'ZP-' . now()->format('YmdHis');
    }
}
