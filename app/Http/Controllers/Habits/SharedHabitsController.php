<?php

namespace App\Http\Controllers\Habits;

use App\Http\Controllers\Controller;
use App\Models\SharedHabit;
use Illuminate\Http\Request;
use Inertia\Inertia;

class SharedHabitsController extends Controller
{
    /**
     * Display pending habit invitations for the authenticated user
     */
    public function index()
    {
        $pendingInvitations = auth()->user()->pendingHabitInvitations()
            ->with('user')
            ->get();

        $acceptedHabits = auth()->user()->acceptedSharedHabits()
            ->with('user')
            ->get();

        return Inertia::render('Habit/SharedHabits', [
            'pendingInvitations' => $pendingInvitations,
            'acceptedHabits' => $acceptedHabits,
        ]);
    }

    /**
     * Accept a habit invitation
     */
    public function accept(Request $request, $habitId)
    {
        $sharedHabit = SharedHabit::where('habit_id', $habitId)
            ->where('shared_with_id', auth()->user()->id)
            ->where('status', 'pending')
            ->firstOrFail();

        $sharedHabit->update(['status' => 'accepted']);

        return back()->with('success', 'Habit invitation accepted!');
    }

    /**
     * Refuse a habit invitation
     */
    public function refuse(Request $request, $habitId)
    {
        $sharedHabit = SharedHabit::where('habit_id', $habitId)
            ->where('shared_with_id', auth()->user()->id)
            ->where('status', 'pending')
            ->firstOrFail();

        $sharedHabit->update(['status' => 'refused']);

        return back()->with('success', 'Habit invitation refused.');
    }

    /**
     * Abandon a shared habit (for accepted habits)
     */
    public function abandon(Request $request, $habitId)
    {
        $sharedHabit = SharedHabit::where('habit_id', $habitId)
            ->where('shared_with_id', auth()->user()->id)
            ->where('status', 'accepted')
            ->firstOrFail();

        $sharedHabit->update(['status' => 'abandoned']);

        return back()->with('success', 'You have left the shared habit.');
    }
}