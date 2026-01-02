<?php

namespace App\Http\Controllers;

use App\Models\ProductionIssue;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class ProductionIssueController extends Controller
{
    /**
     * Get production issues
     */
    public function index(Request $request)
    {
        $query = ProductionIssue::with(['productionOrder', 'reporter', 'resolver']);

        if ($request->has('production_order_id')) {
            $query->where('production_order_id', $request->production_order_id);
        }

        if ($request->has('status')) {
            $query->where('status', $request->status);
        }

        if ($request->has('severity')) {
            $query->where('severity', $request->severity);
        }

        if ($request->boolean('open_only')) {
            $query->open();
        }

        if ($request->boolean('critical_only')) {
            $query->critical();
        }

        $issues = $query->orderBy('created_at', 'desc')->get();

        return response()->json($issues);
    }

    /**
     * Get single issue
     */
    public function show($id)
    {
        $issue = ProductionIssue::with([
            'productionOrder.windows',
            'reporter',
            'resolver'
        ])->findOrFail($id);

        return response()->json($issue);
    }

    /**
     * Update issue status
     */
    public function updateStatus(Request $request, $id)
    {
        $issue = ProductionIssue::findOrFail($id);

        $validated = $request->validate([
            'status' => 'required|in:open,in_progress,resolved,cancelled'
        ]);

        $issue->update(['status' => $validated['status']]);

        return response()->json([
            'message' => 'Issue status updated successfully',
            'issue' => $issue
        ]);
    }

    /**
     * Resolve issue
     */
    public function resolve(Request $request, $id)
    {
        $issue = ProductionIssue::findOrFail($id);

        $issue->resolve(Auth::id());

        return response()->json([
            'message' => 'Issue resolved successfully',
            'issue' => $issue->fresh(['resolver'])
        ]);
    }

    /**
     * Get issue statistics
     */
    public function statistics()
    {
        $stats = [
            'total' => ProductionIssue::count(),
            'open' => ProductionIssue::open()->count(),
            'critical' => ProductionIssue::critical()->count(),
            'high' => ProductionIssue::high()->count(),
            'resolved_today' => ProductionIssue::where('status', 'resolved')
                ->whereDate('resolved_at', today())
                ->count()
        ];

        return response()->json($stats);
    }
}
