<?php

namespace App\Http\Controllers\Habits;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Habit;
use Inertia\Inertia;

class HabitsController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        // For initial load, only get today's data
        $today = now()->format('Y-m-d');
        $habits = $this->getHabitsWithDateRange($today, $today);

        return Inertia::render('Habit/Index', [
            'habits' => $habits,
        ]);
    }

    /**
     * Get habits with user_habits filtered by date range (API endpoint)
     */
    public function getHabitsForDateRange(Request $request)
    {
        $validated = $request->validate([
            'start_date' => ['required', 'date'],
            'end_date' => ['required', 'date', 'after_or_equal:start_date'],
        ]);

        $habits = $this->getHabitsWithDateRange(
            $validated['start_date'],
            $validated['end_date']
        );

        return response()->json(['habits' => $habits]);
    }

    /**
     * Private helper method to get habits with filtered user_habits
     */
    private function getHabitsWithDateRange($startDate, $endDate)
    {
        // Get user's own habits
        $ownHabits = Habit::where('user_id', auth()->user()->id)
            ->with([
                'sharedWith',
                'user' // Load the owner information
            ])
            ->get()
            ->map(function ($habit) use ($startDate, $endDate) {
                return $this->mapHabitWithDateRange($habit, $startDate, $endDate);
            });

        // Get accepted shared habits
        $sharedHabits = auth()->user()->acceptedSharedHabits()
            ->with([
                'sharedWith',
                'user' // Load the owner information
            ])
            ->get()
            ->map(function ($habit) use ($startDate, $endDate) {
                return $this->mapHabitWithDateRange($habit, $startDate, $endDate);
            });

        // Combine both collections
        return $ownHabits->concat($sharedHabits)->values();
    }

    /**
     * Map habit with filtered user_habits by date range
     */
    private function mapHabitWithDateRange($habit, $startDate, $endDate)
    {
        // Load user habits filtered by date range
        $userHabits = $habit->userHabits()
            ->whereBetween('completed_at', [
                $startDate . ' 00:00:00',
                $endDate . ' 23:59:59'
            ])
            ->with('user')
            ->get();

        // Separate current user's habits
        $habit->user_habits = $userHabits
            ->where('user_id', auth()->user()->id)
            ->map(function ($uh) {
                return [
                    'id' => $uh->id,
                    'user_id' => $uh->user_id,
                    'habit_id' => $uh->habit_id,
                    'completed' => $uh->completed,
                    'completed_at' => $uh->completed_at ? $uh->completed_at->toISOString() : null,
                    'media_path' => $uh->media_path,
                    'media_type' => $uh->media_type,
                    'media_caption' => $uh->media_caption,
                ];
            })
            ->values()
            ->toArray();

        // All user habits for shared users
        $habit->all_user_habits = $userHabits
            ->map(function ($uh) {
                return [
                    'id' => $uh->id,
                    'user_id' => $uh->user_id,
                    'habit_id' => $uh->habit_id,
                    'completed' => $uh->completed,
                    'completed_at' => $uh->completed_at ? $uh->completed_at->toISOString() : null,
                    'media_path' => $uh->media_path,
                    'media_type' => $uh->media_type,
                    'media_caption' => $uh->media_caption,
                ];
            })
            ->values()
            ->toArray();

        // Add pivot data (joined_at from updated_at) to shared_with users
        if ($habit->sharedWith && $habit->sharedWith->count() > 0) {
            $sharedWithData = $habit->sharedWith->map(function ($user) {
                return [
                    'id' => $user->id,
                    'name' => $user->name,
                    'email' => $user->email,
                    'joined_at' => $user->pivot->updated_at ? $user->pivot->updated_at->toISOString() : null,
                ];
            })->values()->toArray();

            $habit->shared_with = $sharedWithData;
            unset($habit->sharedWith); // Remove original relation to avoid confusion
        }

        return $habit;
    }

    /**
     * Display statistics page.
     */
    public function statistics()
    {
        // Calculate date range (last 30 days)
        $maxDate = now()->subDays(1)->format('Y-m-d');
        $minDate = now()->subDays(30)->format('Y-m-d');

        // Get statistics for current user
        $currentUserStats = auth()->user()->getHabitStatistics($minDate, $maxDate);

        // Get statistics for friends
        $friends = auth()->user()->cleanFriends();
        $friendsStats = [];

        foreach ($friends as $friend) {
            $friendsStats[] = [
                'user' => [
                    'id' => $friend->id,
                    'name' => $friend->name,
                    'email' => $friend->email,
                ],
                'statistics' => $friend->getHabitStatistics($minDate, $maxDate),
            ];
        }

        return Inertia::render('Habit/Statistics', [
            'currentUser' => [
                'id' => auth()->user()->id,
                'name' => auth()->user()->name,
                'email' => auth()->user()->email,
            ],
            'currentUserStatistics' => $currentUserStats,
            'friendsStatistics' => $friendsStats,
            'dateRange' => [
                'minDate' => $minDate,
                'maxDate' => $maxDate,
            ],
        ]);
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        $friends = auth()->user()->cleanFriends();

        return Inertia::render('Habit/Create', [
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
            'description' => ['nullable', 'string', 'max:1000'],
            'start_date' => ['required', 'date', 'before_or_equal:today'],
            'end_date' => ['required', 'date', 'after_or_equal:start_date'],
            'frequency' => ['required', 'string', 'in:everyday,weekdays,weekends'],
            'color' => ['required', 'string', 'regex:/^#[0-9A-Fa-f]{6}$/'],
            'shared_with' => ['nullable', 'array'],
        ], [
            'name.required' => 'The habit name is required.',
            'name.max' => 'The habit name cannot exceed 255 characters.',
            'description.max' => 'The description cannot exceed 1000 characters.',
            'start_date.required' => 'The start date is required.',
            'start_date.date' => 'Please provide a valid start date.',
            'start_date.before_or_equal' => 'The start date cannot be in the future.',
            'end_date.required' => 'The end date is required.',
            'end_date.date' => 'Please provide a valid end date.',
            'end_date.after_or_equal' => 'The end date must be after or equal to the start date.',
            'frequency.required' => 'Please select a frequency.',
            'frequency.in' => 'Please select a valid frequency option.',
            'color.required' => 'Please select a color.',
            'color.regex' => 'Please provide a valid hexadecimal color (e.g., #93C5FD).',
        ]);

        // Validate shared_with users are friends
        $validationError = $this->validateSharedWith($validated['shared_with'] ?? []);
        if ($validationError) {
            return $validationError;
        }

        $validated['user_id'] = auth()->user()->id;

        $habit = Habit::create($validated);

        // Sync shared users
        if (!empty($validated['shared_with'])) {
            $syncData = collect($validated['shared_with'])->pluck('id')->toArray();
            $habit->sharedWith()->sync($syncData);
        }

        return redirect()->route('habits.index')->with('success', 'Habit created successfully.');
    }

    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        $habit = Habit::where('user_id', auth()->user()->id)->findOrFail($id);

        return Inertia::render('Habit/Show', [
            'habit' => $habit,
        ]);
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(string $id)
    {
        $habit = Habit::with('sharedWith')->where('user_id', auth()->user()->id)->findOrFail($id);
        $friends = auth()->user()->cleanFriends();

        return Inertia::render('Habit/Edit', [
            'habit' => $habit,
            'friends' => $friends,
        ]);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $id)
    {
        $habit = Habit::where('user_id', auth()->user()->id)->findOrFail($id);

        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'description' => ['nullable', 'string', 'max:1000'],
            'start_date' => ['required', 'date'],
            'end_date' => ['required', 'date', 'after_or_equal:start_date'],
            'frequency' => ['required', 'string', 'in:everyday,weekdays,weekends'],
            'color' => ['required', 'string', 'regex:/^#[0-9A-Fa-f]{6}$/'],
            'shared_with' => ['nullable', 'array'],
        ], [
            'name.required' => 'The habit name is required.',
            'name.max' => 'The habit name cannot exceed 255 characters.',
            'description.max' => 'The description cannot exceed 1000 characters.',
            'start_date.required' => 'The start date is required.',
            'start_date.date' => 'Please provide a valid start date.',
            'end_date.required' => 'The end date is required.',
            'end_date.date' => 'Please provide a valid end date.',
            'end_date.after_or_equal' => 'The end date must be after or equal to the start date.',
            'frequency.required' => 'Please select a frequency.',
            'frequency.in' => 'Please select a valid frequency option.',
            'color.required' => 'Please select a color.',
            'color.regex' => 'Please provide a valid hexadecimal color (e.g., #93C5FD).',
        ]);

        // Validate shared_with users are friends
        $validationError = $this->validateSharedWith($validated['shared_with'] ?? []);
        if ($validationError) {
            return $validationError;
        }

        $habit->update($validated);

        // Sync shared users
        $syncData = !empty($validated['shared_with'])
            ? collect($validated['shared_with'])->pluck('id')->toArray()
            : [];
        $habit->sharedWith()->sync($syncData);

        return redirect()->route('habits.index')->with('success', 'Habit updated successfully.');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        $habit = Habit::where('user_id', auth()->user()->id)->findOrFail($id);
        $habit->delete();

        return redirect()->route('habits.index')->with('success', 'Habit deleted successfully.');
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
                    'shared_with' => 'You can only share habits with your friends.'
                ])->withInput();
            }
        }

        return null;
    }
}
