<?php

namespace App\Services;

use App\Models\Material;
use Illuminate\Support\Collection;

class DashboardService
{
    /**
     * Pobierz statystyki dla dashboardu
     */
    public function getStatistics(): array
    {
        return [
            'production_orders' => $this->getProductionOrdersStats(),
            'windows' => $this->getWindowsStats(),
            'materials' => $this->getMaterialsStats(),
            'low_stock_alerts' => $this->getLowStockAlerts(),
        ];
    }

    private function getProductionOrdersStats(): array
    {
        return [
            'total' => \App\Models\ProductionOrder::count(),
            'nowe' => \App\Models\ProductionOrder::where('status', 'nowe')->count(),
            'w_trakcie' => \App\Models\ProductionOrder::where('status', 'w_trakcie')->count(),
            'zakonczone' => \App\Models\ProductionOrder::whereDate('completed_at', today())->count(),
        ];
    }

    private function getWindowsStats(): array
    {
        return [
            'total' => \App\Models\Window::count(),
            'projekt' => \App\Models\Window::where('status', 'projekt')->count(),
            'w_produkcji' => \App\Models\Window::where('status', 'w_produkcji')->count(),
            'gotowe' => \App\Models\Window::where('status', 'gotowe')->count(),
            'wydane' => \App\Models\Window::where('status', 'wydane')->count(),
        ];
    }

    private function getMaterialsStats(): array
    {
        $materials = Material::all();
        
        return [
            'total_value' => $materials->sum(function ($material) {
                return $material->current_stock * $material->price_per_unit;
            }),
            'low_stock_count' => $materials->filter->isLowStock()->count(),
            'by_type' => Material::selectRaw('type, SUM(current_stock * price_per_unit) as value')
                ->groupBy('type')
                ->get()
                ->pluck('value', 'type'),
        ];
    }

    private function getLowStockAlerts(): Collection
    {
        return Material::all()
            ->filter->isLowStock()
            ->values();
    }

    /**
     * Export danych magazynowych do CSV
     */
    public function exportMaterialsToCSV(): string
    {
        $materials = Material::all();
        
        $csv = "ID,Nazwa,Typ,Jednostka,Stan,Min. Stan,Cena,Dostawca\n";
        
        foreach ($materials as $material) {
            $csv .= implode(',', [
                $material->id,
                '"' . $material->name . '"',
                $material->type,
                $material->unit,
                $material->current_stock,
                $material->min_stock,
                $material->price_per_unit,
                '"' . ($material->supplier ?? '') . '"',
            ]) . "\n";
        }
        
        return $csv;
    }
}
