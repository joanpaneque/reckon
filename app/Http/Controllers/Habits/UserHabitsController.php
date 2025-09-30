<?php

namespace App\Http\Controllers\Habits;

use App\Http\Controllers\Controller;
use App\Models\Habit;
use App\Models\UserHabit;
use Illuminate\Http\Request;
use Carbon\Carbon;

class UserHabitsController extends Controller
{
    /**
     * Create or update a user habit completion for a specific date
     */
    public function createOrUpdate(Request $request, $habitId)
    {
        $validated = $request->validate([
            'completed' => 'required|boolean',
            'date' => 'required|date',
        ]);

        // Verify the user can access this habit (either owner or shared with them)
        $habit = Habit::where(function ($query) use ($habitId) {
            $query->where('user_id', auth()->id()) // User is the owner
                  ->orWhereHas('sharedWith', function ($subQuery) { // Or habit is shared with user
                      $subQuery->where('users.id', auth()->id())
                               ->where('shared_habits.status', 'accepted');
                  });
        })->findOrFail($habitId);

        $date = Carbon::parse($validated['date'])->startOfDay();
        $userId = auth()->id();

        // Find existing UserHabit record for this user, habit, and date
        $userHabit = UserHabit::where('user_id', $userId)
                             ->where('habit_id', $habitId)
                             ->whereDate('completed_at', $date)
                             ->first();

        if ($userHabit) {
            // Update existing record
            $userHabit->update([
                'completed' => $validated['completed'],
                'completed_at' => $validated['completed'] ? $date : null,
            ]);
        } else if ($validated['completed']) {
            // Only create new record if marking as completed
            UserHabit::create([
                'user_id' => $userId,
                'habit_id' => $habitId,
                'completed' => true,
                'completed_at' => $date,
            ]);
        }

        return redirect()->back();
    }
}
