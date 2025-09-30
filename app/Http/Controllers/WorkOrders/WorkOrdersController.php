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
        $workOrders = WorkOrder::with('sharedWith')
            ->where('user_id', auth()->user()->id)
            ->orderBy('created_at', 'desc')
            ->paginate(10, ['*'], 'own_page');

        // Add time and cost calculations to each work order
        $workOrders->getCollection()->transform(function ($workOrder) {
            $timeAndCost = $workOrder->getTotalTimeAndCost();
            $workOrder->total_seconds = $timeAndCost['totalSeconds'];
            $workOrder->total_cost = $timeAndCost['totalCost'];
            return $workOrder;
        });

        // Get work orders shared with the authenticated user (excluding own work orders)
        $sharedWorkOrders = WorkOrder::with(['sharedWith', 'user'])
            ->where('user_id', '!=', auth()->user()->id)
            ->whereHas('sharedWith', function ($query) {
                $query->where('users.id', auth()->user()->id);
            })
            ->orderBy('created_at', 'desc')
            ->paginate(10, ['*'], 'shared_page');

        // Add time and cost calculations to shared work orders
        $sharedWorkOrders->getCollection()->transform(function ($workOrder) {
            $timeAndCost = $workOrder->getTotalTimeAndCost();
            $workOrder->total_seconds = $timeAndCost['totalSeconds'];
            $workOrder->total_cost = $timeAndCost['totalCost'];
            return $workOrder;
        });

        return Inertia::render('WorkOrder/Index', [
            'workOrders' => $workOrders,
            'sharedWorkOrders' => $sharedWorkOrders,
        ]);
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        $friends = auth()->user()->cleanFriends();

        return Inertia::render('WorkOrder/Create', [
            'friends' => $friends,
        ]);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'hour_price' => ['required', 'numeric', 'min:0'],
            'shared_with' => ['nullable', 'array'],
        ]);

        // Validate shared_with users are friends
        $validationError = $this->validateSharedWith($validated['shared_with'] ?? []);
        if ($validationError) {
            return $validationError;
        }

        $workOrder = WorkOrder::create([...$validated, 'user_id' => auth()->user()->id]);

        // Sync shared users with permissions
        if (!empty($validated['shared_with'])) {
            $syncData = collect($validated['shared_with'])->mapWithKeys(function ($user) {
                return [$user['id'] => ['permission' => $user['permission'] ?? 'view']];
            })->toArray();
            $workOrder->sharedWith()->sync($syncData);
        }

        return redirect()->route('work-orders.index');
    }

    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        $workOrder = WorkOrder::with('sharedWith')->findOrFail($id);


        if (!$this->checkPermission($workOrder)) {
            return abort(404);
        }

        $workOrderEntries = $workOrder->entries()->orderBy('created_at', 'desc')->paginate(10);

        // Use the model method for consistency
        $timeAndCost = $workOrder->getTotalTimeAndCost();

        return Inertia::render('WorkOrder/Show', [
            'workOrder' => $workOrder,
            'workOrderEntries' => $workOrderEntries,
            'totalTime' => $timeAndCost['totalSeconds'],
            'totalCost' => $timeAndCost['totalCost'],
            'canEdit' => self::checkEditPermission($workOrder),
        ]);
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(string $id)
    {
        $workOrder = WorkOrder::with('sharedWith')->findOrFail($id);
        $friends = auth()->user()->cleanFriends();


        return Inertia::render('WorkOrder/Edit', [
            'workOrder' => $workOrder,
            'friends' => $friends,
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
            'shared_with' => ['nullable', 'array'],
        ]);


        // Validate shared_with users are friends
        $validationError = $this->validateSharedWith($validated['shared_with'] ?? []);
        if ($validationError) {
            return $validationError;
        }

        $workOrder = WorkOrder::where('user_id', auth()->user()->id)->findOrFail($id);
        $workOrder->update($validated);

        // Sync shared users with permissions
        $syncData = !empty($validated['shared_with'])
            ? collect($validated['shared_with'])->mapWithKeys(function ($user) {
                return [$user['id'] => ['permission' => $user['permission'] ?? 'view']];
            })->toArray()
            : [];
        $workOrder->sharedWith()->sync($syncData);

        return redirect()->route('work-orders.index');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        $workOrder = WorkOrder::where('user_id', auth()->user()->id)->findOrFail($id);
        $workOrder->delete();

        return redirect()->route('work-orders.index');
    }

    /**
     * Validate that all users in shared_with are friends of the authenticated user
     */
    private function validateSharedWith(array $sharedWith)
    {
        if (empty($sharedWith)) {
            return null;
        }

        $friendIds = auth()->user()->cleanFriends()->pluck('id')->toArray();


        foreach ($sharedWith as $user) {
            if (!in_array($user['id'], $friendIds)) {
                return back()->withErrors([
                    'shared_with' => 'You can only share work orders with your friends.'
                ])->withInput();
            }
        }

        return null;
    }


    private function checkPermission(WorkOrder $workOrder)
    {
        if ($workOrder->user_id === auth()->user()->id) {
            return true;
        }

        if ($workOrder->sharedWith->contains('id', auth()->user()->id)) {
            return true;
        }

        return false;
    }

    public static function checkEditPermission(WorkOrder $workOrder)
    {
        // Owner always has edit permission
        if ($workOrder->user_id === auth()->user()->id) {
            return true;
        }

        // Check if user has edit permission through sharing
        $sharedUser = $workOrder->sharedWith->firstWhere('id', auth()->user()->id);
        if ($sharedUser && $sharedUser->pivot->permission === 'edit') {
            return true;
        }

        return false;
    }
}
