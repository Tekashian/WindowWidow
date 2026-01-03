<?php

namespace App\Http\Controllers;

use App\Models\ProductionBatch;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Auth;

class ProductionBatchController extends Controller
{
    /**
     * Get batches for specific production order
     */
    public function index(Request $request)
    {
        $query = ProductionBatch::with(['productionOrder', 'deliveries']);

        if ($request->has('production_order_id')) {
            $query->where('production_order_id', $request->production_order_id);
        }

        if ($request->has('status')) {
            $query->where('status', $request->status);
        }

        $batches = $query->orderBy('created_at', 'desc')->get();

        return new JsonResponse($batches);
    }

    /**
     * Update batch status
     */
    public function updateStatus(Request $request, $id)
    {
        $batch = ProductionBatch::findOrFail($id);

        $validated = $request->validate([
            'status' => 'required|in:in_production,quality_check,ready,shipped,cancelled',
            'quality_check_passed' => 'nullable|boolean',
            'quality_notes' => 'nullable|string'
        ]);

        $updateData = ['status' => $validated['status']];

        if (isset($validated['quality_check_passed'])) {
            $updateData['quality_check_passed'] = $validated['quality_check_passed'];
        }

        if (isset($validated['quality_notes'])) {
            $updateData['quality_notes'] = $validated['quality_notes'];
        }

        if ($validated['status'] === 'ready') {
            $updateData['completed_at'] = now();
        }

        $batch->update($updateData);

        return new JsonResponse([
            'message' => 'Batch status updated successfully',
            'batch' => $batch
        ]);
    }

    /**
     * Get batch details
     */
    public function show($id)
    {
        $batch = ProductionBatch::with([
            'productionOrder.windows',
            'deliveries.shipper',
            'deliveries.receiver'
        ])->findOrFail($id);

        return new JsonResponse($batch);
    }
}
