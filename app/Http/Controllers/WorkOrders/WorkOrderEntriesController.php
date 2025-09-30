<?php

namespace App\Http\Controllers\WorkOrders;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Inertia\Inertia;
use App\Models\WorkOrder;
use App\Models\WorkOrderEntry;
use App\Http\Controllers\WorkOrders\WorkOrdersController;

class WorkOrderEntriesController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(string $workOrder)
    {
        $workOrderModel = WorkOrder::findOrFail($workOrder);
        $workOrderEntries = $workOrderModel->entries()->orderBy('created_at', 'desc')->paginate(10);

        return Inertia::render('WorkOrder/Entry/Index', [
            'workOrder' => $workOrderModel,
            'workOrderEntries' => $workOrderEntries,
        ]);
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create(string $workOrder)
    {
        $workOrderModel = WorkOrder::findOrFail($workOrder);

        return Inertia::render('WorkOrder/Entry/Create', [
            'workOrder' => $workOrderModel,
        ]);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request, string $workOrder)
    {
        $workOrderModel = WorkOrder::findOrFail($workOrder);

        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'description' => ['nullable', 'string', 'max:1000'],
            'started_at' => ['required', 'date'],
            'ended_at' => ['nullable', 'date'],
        ]);

        $validated['work_order_id'] = $workOrder;
        $workOrderEntry = WorkOrderEntry::create([...$validated, 'created_by' => auth()->user()->id]);

        return redirect()->route('work-orders.show', $workOrder);
    }

    /**
     * Display the specified resource.
     */
    public function show(string $workOrder, string $entry)
    {
        $workOrderModel = WorkOrder::with('sharedWith')->findOrFail($workOrder);
        $workOrderEntry = $workOrderModel->entries()->findOrFail($entry);

        return Inertia::render('WorkOrder/Entry/Show', [
            'workOrder' => $workOrderModel,
            'workOrderEntry' => $workOrderEntry,
            'canEdit' => WorkOrdersController::checkEditPermission($workOrderModel),
        ]);
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(string $workOrder, string $entry)
    {
        $workOrderModel = WorkOrder::with('sharedWith')->findOrFail($workOrder);

        if (!WorkOrdersController::checkEditPermission($workOrderModel)) {
            abort(404);
        }

        $workOrderEntry = $workOrderModel->entries()->findOrFail($entry);

        return Inertia::render('WorkOrder/Entry/Edit', [
            'workOrder' => $workOrderModel,
            'workOrderEntry' => $workOrderEntry,
        ]);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $workOrder, string $entry)
    {
        $workOrderModel = WorkOrder::with('sharedWith')->findOrFail($workOrder);

        if (!WorkOrdersController::checkEditPermission($workOrderModel)) {
            abort(404);
        }

        $workOrderEntry = $workOrderModel->entries()->findOrFail($entry);

        // Si solo se envía ended_at (para stop timer), hacer update directo
        if ($request->has('ended_at') && count($request->all()) === 1) {
            $workOrderEntry->update([
                'ended_at' => $request->ended_at,
            ]);
            return redirect()->back();
        }

        // Validación completa para edits normales
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'description' => ['nullable', 'string', 'max:1000'],
            'started_at' => ['required', 'date'],
            'ended_at' => ['nullable', 'date'],
        ]);

        $workOrderEntry->update($validated);

        return redirect()->route('work-orders.show', $workOrder);
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $workOrder, string $entry)
    {
        $workOrderModel = WorkOrder::with('sharedWith')->findOrFail($workOrder);

        if (!WorkOrdersController::checkEditPermission($workOrderModel)) {
            abort(404);
        }

        $workOrderEntry = $workOrderModel->entries()->findOrFail($entry);
        $workOrderEntry->delete();

        return redirect()->route('work-orders.show', $workOrder);
    }
}
