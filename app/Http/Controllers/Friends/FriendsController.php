<?php

namespace App\Http\Controllers\Friends;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Inertia\Inertia;
use App\Models\User;
use App\Models\Friendship;
use App\Models\Motivation;


class FriendsController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $incomingAndFriends = auth()->user()->incomingAndFriends()->paginate(10);

        return Inertia::render('Friend/Index', [
            'incomingAndFriends' => $incomingAndFriends,
        ]);
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        return Inertia::render('Friend/Create');
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'email' => ['required', 'email', 'exists:users,email', 'not_in:' . auth()->user()->email],
        ]);

        $friend = User::where('email', $validated['email'])->first();
        $friendship = Friendship::create([
            'sender_id' => auth()->user()->id,
            'receiver_id' => $friend->id,
            'status' => 'pending',
        ]);

        return redirect()->route('friends.index');
    }

    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        $friendship = Friendship::with(['sender', 'receiver'])->findOrFail($id);
        
        // Determine who is the friend (the other person in the friendship)
        $currentUserId = auth()->id();
        $friendId = $friendship->sender_id === $currentUserId 
            ? $friendship->receiver_id 
            : $friendship->sender_id;
        
        // Get motivations received from this friend
        $motivationsReceived = Motivation::where('user_id', $friendId)
            ->where('sent_to', $currentUserId)
            ->with('sender')
            ->orderBy('created_at', 'desc')
            ->get();
        
        // Get motivations sent to this friend
        $motivationsSent = Motivation::where('user_id', $currentUserId)
            ->where('sent_to', $friendId)
            ->with('receiver')
            ->orderBy('created_at', 'desc')
            ->get();
        
        return Inertia::render('Friend/Show', [
            'friendship' => $friendship,
            'motivationsReceived' => $motivationsReceived,
            'motivationsSent' => $motivationsSent,
            'friendId' => $friendId,
        ]);
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(string $id)
    {
        $friendship = Friendship::findOrFail($id);
        return Inertia::render('Friend/Edit', [
            'friendship' => $friendship,
        ]);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $id)
    {
        // validate, you can only change the status to rejected only if it was pending
        $validated = $request->validate([
            'status' => ['required', 'in:accepted,rejected'],
        ]);

        $friendship = Friendship::findOrFail($id);

        if ($friendship->status === 'pending') {
            $friendship->update($validated);
        }

        return redirect()->route('friends.index');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        $friendship = Friendship::findOrFail($id);
        $friendship->delete();
        return redirect()->route('friends.index');
    }
}
