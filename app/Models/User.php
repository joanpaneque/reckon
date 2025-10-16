<?php

namespace App\Models;

use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Illuminate\Support\Facades\DB;

class User extends Authenticatable
{
    use Notifiable;

    protected $fillable = [
        'name',
        'email',
        'password',
    ];

    protected $hidden = [
        'password',
    ];

    protected function casts(): array
    {
        return [
            'password' => 'hashed',
        ];
    }

    public function sharedWithMeWorkOrders()
    {
        return $this->belongsToMany(
            WorkOrder::class,
            'shared_work_orders',
            'shared_with_id',
            'work_order_id'
        )->withPivot('permission')->withTimestamps();
    }

    public function workOrderEntries()
    {
        return $this->hasMany(WorkOrderEntry::class);
    }

    public function sharedWithMeHabits()
    {
        return $this->belongsToMany(
            Habit::class,
            'shared_habits',
            'shared_with_id',
            'habit_id'
        )->withPivot('status')->withTimestamps();
    }

    public function acceptedSharedHabits()
    {
        return $this->belongsToMany(
            Habit::class,
            'shared_habits',
            'shared_with_id',
            'habit_id'
        )->wherePivot('status', 'accepted')
        ->withPivot('status')->withTimestamps();
    }

    public function pendingHabitInvitations()
    {
        return $this->belongsToMany(
            Habit::class,
            'shared_habits',
            'shared_with_id',
            'habit_id'
        )->wherePivot('status', 'pending')
        ->withPivot('status')->withTimestamps();
    }

    public function sentFriendships()
    {
        return $this->hasMany(Friendship::class, 'sender_id');
    }

    public function receivedFriendships()
    {
        return $this->hasMany(Friendship::class, 'receiver_id');
    }

    // Scope para obtener todas las friendships (enviadas y recibidas)
    public function scopeAllFriendships($query, $userId)
    {
        return Friendship::where('sender_id', $userId)
            ->orWhere('receiver_id', $userId);
    }

    // Método para obtener friendships pendientes recibidas y aceptadas
    public function incomingAndFriends()
    {
        return Friendship::query()
            ->where(function ($query) {
                // Pendientes recibidas
                $query->where('receiver_id', $this->id)
                      ->where('status', 'pending');
            })
            ->orWhere(function ($query) {
                // Aceptadas (enviadas o recibidas)
                $query->where('status', 'accepted')
                      ->where(function ($q) {
                          $q->where('sender_id', $this->id)
                            ->orWhere('receiver_id', $this->id);
                      });
            })
            ->orderBy('created_at', 'desc');
    }


    public function cleanFriends()
    {
        return Friendship::where('status', 'accepted')
            ->where(function ($query) {
                $query->where('sender_id', $this->id)
                      ->orWhere('receiver_id', $this->id);
            })
            ->get()
            ->map(function ($friendship) {
                return $friendship->sender_id === $this->id
                    ? $friendship->receiver
                    : $friendship->sender;
            });
    }

    /**
     * Get habit statistics for a date range
     * Returns an array with completed and failed habits count for each day
     *
     * This function ONLY counts habits where THIS specific user is participating:
     * 1. Habits created BY this user (owner)
     * 2. Habits shared WITH this user that have been ACCEPTED
     *
     * Takes into account the date when the user joined each habit:
     * - Own habits: joined_at = start_date (when created)
     * - Shared habits: joined_at = acceptance date (when accepted invitation)
     *
     * @param string $minDate Date in Y-m-d format
     * @param string $maxDate Date in Y-m-d format
     * @return array Array of objects with date, habitsCompletedCount, and habitsFailedCount
     */
    public function getHabitStatistics($minDate, $maxDate)
    {
        // Convert dates to Carbon instances
        $startDate = \Carbon\Carbon::parse($minDate)->startOfDay();
        $endDate = \Carbon\Carbon::parse($maxDate)->startOfDay();

        // Get all habits created by this user with their start dates
        $ownHabits = Habit::where('user_id', $this->id)->get()->map(function ($habit) {
            $habit->joined_at = $habit->start_date; // User joined when they created it
            $habit->is_owner = true;
            return $habit;
        });

        // Get all accepted shared habits for this user with the acceptance date
        // Only habits where THIS user has explicitly accepted the invitation
        $sharedHabits = Habit::whereHas('sharedWith', function ($query) {
            $query->where('shared_with_id', $this->id)
                  ->where('status', 'accepted');
        })->get()->map(function ($habit) {
            // Get the pivot data to know when user accepted the habit
            $pivotData = DB::table('shared_habits')
                ->where('habit_id', $habit->id)
                ->where('shared_with_id', $this->id)
                ->where('status', 'accepted')
                ->first();

            // Use the updated_at from pivot (when they accepted) as joined date
            $habit->joined_at = $pivotData ? $pivotData->updated_at : $habit->start_date;
            $habit->is_owner = false;
            return $habit;
        });

        // Combine all habits (own + accepted shared)
        $allHabits = $ownHabits->concat($sharedHabits);

        // Create a set of habit IDs that this user has access to
        $allowedHabitIds = $allHabits->pluck('id')->toArray();

        // Get all user habit completions for this user in the date range
        $userHabitCompletions = \App\Models\UserHabit::where('user_id', $this->id)
            ->whereBetween('completed_at', [$minDate, $maxDate])
            ->get()
            ->groupBy(function ($item) {
                return \Carbon\Carbon::parse($item->completed_at)->format('Y-m-d');
            });

        // Initialize result array
        $result = [];
        $currentDate = $startDate->copy();

        // Iterate through each day in the range
        while ($currentDate->lte($endDate)) {
            $dateString = $currentDate->format('Y-m-d');
            $dayOfWeek = $currentDate->dayOfWeek; // 0 = Sunday, 6 = Saturday

            $completedCount = 0;
            $failedCount = 0;

            // Get completions for this specific date (only for allowed habits)
            $completionsThisDay = $userHabitCompletions->get($dateString, collect());
            $completedHabitIds = $completionsThisDay
                ->filter(function ($completion) use ($allowedHabitIds) {
                    // Only count completions for habits this user has access to
                    return in_array($completion->habit_id, $allowedHabitIds);
                })
                ->pluck('habit_id')
                ->toArray();

            // Check each habit (only habits user has access to)
            foreach ($allHabits as $habit) {
                // Double check: ensure habit ID is in allowed list
                if (!in_array($habit->id, $allowedHabitIds)) {
                    continue; // Skip habits user doesn't have access to
                }
                // Check if habit is active on this date
                $habitStartDate = \Carbon\Carbon::parse($habit->start_date)->startOfDay();
                $habitEndDate = \Carbon\Carbon::parse($habit->end_date)->startOfDay();
                $habitJoinedDate = \Carbon\Carbon::parse($habit->joined_at)->startOfDay();

                // Skip if current date is before habit start date or after habit end date
                if ($currentDate->lt($habitStartDate) || $currentDate->gt($habitEndDate)) {
                    continue; // Habit not active on this date
                }

                // Skip if user hasn't joined this habit yet (neutral: +0, -0)
                if ($currentDate->lt($habitJoinedDate)) {
                    continue; // User hasn't joined this habit yet
                }

                // Check if this day matches the habit's frequency
                $shouldTrack = false;

                switch ($habit->frequency) {
                    case 'everyday':
                        $shouldTrack = true;
                        break;
                    case 'weekdays':
                        // Monday (1) to Friday (5)
                        $shouldTrack = $dayOfWeek >= 1 && $dayOfWeek <= 5;
                        break;
                    case 'weekends':
                        // Saturday (6) and Sunday (0)
                        $shouldTrack = $dayOfWeek === 0 || $dayOfWeek === 6;
                        break;
                }

                if (!$shouldTrack) {
                    continue; // This day doesn't match the frequency
                }

                // Check if this habit was completed on this date
                if (in_array($habit->id, $completedHabitIds)) {
                    $completedCount++;
                } else {
                    $failedCount++;
                }
            }

            // Add day to result
            $result[] = [
                'date' => $dateString,
                'habitsCompletedCount' => $completedCount,
                'habitsFailedCount' => $failedCount,
            ];

            // Move to next day
            $currentDate->addDay();
        }

        return $result;
    }

}
