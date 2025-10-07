<?php

namespace App\Http\Controllers;

use App\Models\Motivation;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class MotivationController extends Controller
{
    // Enviar una motivación
    public function store(Request $request)
    {
        $validated = $request->validate([
            'sent_to' => 'required|exists:users,id',
            'message' => 'required|string|max:1000',
            'image' => 'nullable|image|max:10240', // 10MB max
        ]);

        $imagePath = null;
        if ($request->hasFile('image')) {
            $imagePath = $request->file('image')->store('motivations', 'public');
        }

        $motivation = Motivation::create([
            'user_id' => auth()->id(),
            'sent_to' => $validated['sent_to'],
            'message' => $validated['message'],
            'image_path' => $imagePath,
        ]);

        return back()->with('success', 'Motivation sent successfully!');
    }

    // Obtener motivaciones no leídas del usuario actual
    public function getUnreadMotivations()
    {
        $motivations = Motivation::where('sent_to', auth()->id())
            ->where('receiver_closed', false)
            ->with('sender')
            ->orderBy('created_at', 'desc')
            ->get();

        return response()->json($motivations);
    }

    // Obtener respuestas no leídas (motivaciones donde el usuario es el emisor)
    public function getUnreadResponses()
    {
        $responses = Motivation::where('user_id', auth()->id())
            ->where('receiver_closed', true)
            ->whereNotNull('receiver_message')
            ->where('sender_closed_response', false)
            ->with('receiver')
            ->orderBy('updated_at', 'desc')
            ->get();

        return response()->json($responses);
    }

    // Cerrar una motivación (con o sin respuesta)
    public function closeMotivation(Request $request, Motivation $motivation)
    {
        // Verificar que el usuario actual es el receptor
        if ($motivation->sent_to !== auth()->id()) {
            abort(403);
        }

        $validated = $request->validate([
            'receiver_message' => 'nullable|string|max:1000',
        ]);

        $motivation->update([
            'receiver_closed' => true,
            'receiver_message' => $validated['receiver_message'] ?? null,
        ]);

        return response()->json(['success' => true]);
    }

    // Cerrar la respuesta de una motivación
    public function closeResponse(Motivation $motivation)
    {
        // Verificar que el usuario actual es el emisor
        if ($motivation->user_id !== auth()->id()) {
            abort(403);
        }

        $motivation->update([
            'sender_closed_response' => true,
        ]);

        return response()->json(['success' => true]);
    }
}
