<?php

namespace App\Http\Controllers\WorkOrders;

use App\Http\Controllers\Controller;
use App\Models\WorkOrder;
use Illuminate\Http\Request;

use Inertia\Inertia;

class WorkOrdersController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $workOrders = WorkOrder::orderBy('created_at', 'desc')->paginate(10);

        // Add time and cost calculations to each work order
        $workOrders->getCollection()->transform(function ($workOrder) {
            $timeAndCost = $workOrder->getTotalTimeAndCost();
            $workOrder->total_seconds = $timeAndCost['totalSeconds'];
            $workOrder->total_cost = $timeAndCost['totalCost'];
            return $workOrder;
        });

        return Inertia::render('WorkOrder/Index', [
            'workOrders' => $workOrders,
        ]);
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        return Inertia::render('WorkOrder/Create');
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'hour_price' => ['required', 'numeric', 'min:0'],
        ]);

        $workOrder = WorkOrder::create($validated);

        return redirect()->route('work-orders.index');
    }

    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        $workOrder = WorkOrder::findOrFail($id);
        $workOrderEntries = $workOrder->entries()->orderBy('created_at', 'desc')->paginate(10);

        // Use the model method for consistency
        $timeAndCost = $workOrder->getTotalTimeAndCost();

        return Inertia::render('WorkOrder/Show', [
            'workOrder' => $workOrder,
            'workOrderEntries' => $workOrderEntries,
            'totalTime' => $timeAndCost['totalSeconds'],
            'totalCost' => $timeAndCost['totalCost'],
        ]);
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(string $id)
    {
        $workOrder = WorkOrder::findOrFail($id);

        return Inertia::render('WorkOrder/Edit', [
            'workOrder' => $workOrder,
        ]);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $id)
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'hour_price' => ['required', 'numeric', 'min:0'],
        ]);

        $workOrder = WorkOrder::findOrFail($id);
        $workOrder->update($validated);

        return redirect()->route('work-orders.index');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        $workOrder = WorkOrder::findOrFail($id);
        $workOrder->delete();

        return redirect()->route('work-orders.index');
    }
}
