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

    /**
     * Upload media (photo/video) for a completed habit
     */
    public function uploadMedia(Request $request, $userHabitId)
    {
        $validated = $request->validate([
            'media' => 'required|file|mimes:jpg,jpeg,png,gif,mp4,mov,avi|max:51200', // 50MB max
            'caption' => 'nullable|string|max:1000',
        ]);

        // Find the user habit and verify ownership
        $userHabit = UserHabit::where('id', $userHabitId)
                             ->where('user_id', auth()->id())
                             ->where('completed', true)
                             ->firstOrFail();

        // Store the file
        $file = $request->file('media');
        $extension = $file->getClientOriginalExtension();
        $filename = 'habit_' . $userHabitId . '_' . time() . '.' . $extension;
        $path = $file->storeAs('habit_media', $filename, 'public');

        // Determine media type
        $mediaType = in_array($extension, ['jpg', 'jpeg', 'png', 'gif']) ? 'image' : 'video';

        // Delete old media if exists
        if ($userHabit->media_path) {
            \Storage::disk('public')->delete($userHabit->media_path);
        }

        // Update user habit with media info
        $userHabit->update([
            'media_path' => $path,
            'media_type' => $mediaType,
            'media_caption' => $validated['caption'] ?? null,
        ]);

        return redirect()->back()->with('success', 'Media uploaded successfully!');
    }

    /**
     * Delete media from a completed habit
     */
    public function deleteMedia($userHabitId)
    {
        // Find the user habit and verify ownership
        $userHabit = UserHabit::where('id', $userHabitId)
                             ->where('user_id', auth()->id())
                             ->where('completed', true)
                             ->firstOrFail();

        // Delete the file from storage if exists
        if ($userHabit->media_path) {
            \Storage::disk('public')->delete($userHabit->media_path);
        }

        // Clear media fields
        $userHabit->update([
            'media_path' => null,
            'media_type' => null,
            'media_caption' => null,
        ]);

        return redirect()->back()->with('success', 'Media deleted successfully!');
    }
}
