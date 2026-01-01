<?php

namespace App\Http\Controllers\Todos;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Todo;
use Inertia\Inertia;
use Carbon\Carbon;

class TodosController extends Controller
{
    /**
     * Display the daily todos view
     */
    public function index()
    {
        // For initial load, only get today's data
        $today = now()->format('Y-m-d');
        $todos = $this->getTodosForDate($today);

        return Inertia::render('Todo/Index', [
            'todos' => $todos,
        ]);
    }

    /**
     * Get todos for a specific date (API endpoint)
     */
    public function getTodosForDateRange(Request $request)
    {
        $validated = $request->validate([
            'start_date' => ['required', 'date'],
            'end_date' => ['required', 'date', 'after_or_equal:start_date'],
        ]);

        $todos = [];
        $startDate = Carbon::parse($validated['start_date']);
        $endDate = Carbon::parse($validated['end_date']);
        
        $currentDate = $startDate->copy();
        while ($currentDate->lte($endDate)) {
            $dateString = $currentDate->format('Y-m-d');
            $todos[$dateString] = $this->getTodosForDate($dateString);
            $currentDate->addDay();
        }

        return response()->json(['todos' => $todos]);
    }

    /**
     * Private helper method to get todos for a specific date
     */
    private function getTodosForDate($date)
    {
        return Todo::where('user_id', auth()->user()->id)
            ->whereDate('date', $date)
            ->orderBy('completed')
            ->orderBy('created_at')
            ->get()
            ->map(function ($todo) {
                return [
                    'id' => $todo->id,
                    'user_id' => $todo->user_id,
                    'date' => $todo->date->format('Y-m-d'),
                    'title' => $todo->title,
                    'description' => $todo->description,
                    'completed' => $todo->completed,
                    'completed_at' => $todo->completed_at ? $todo->completed_at->toISOString() : null,
                    'created_at' => $todo->created_at->toISOString(),
                    'updated_at' => $todo->updated_at->toISOString(),
                ];
            })
            ->values()
            ->toArray();
    }

    /**
     * Show the form for creating todos for a specific date
     */
    public function create(Request $request)
    {
        $validated = $request->validate([
            'date' => ['required', 'date'],
        ]);

        $date = Carbon::parse($validated['date'])->format('Y-m-d');

        // Check if todos can be edited for this date
        if (!Todo::canEditTodosForDate($date)) {
            return redirect()->route('todos.index')->withErrors([
                'date' => 'No se pueden crear o editar todos para esta fecha. Los todos deben crearse antes de las 2 AM del día correspondiente.'
            ]);
        }

        // Get existing todos for this date
        $existingTodos = Todo::where('user_id', auth()->user()->id)
            ->whereDate('date', $date)
            ->orderBy('completed')
            ->orderBy('created_at')
            ->get();

        return Inertia::render('Todo/Create', [
            'date' => $date,
            'existingTodos' => $existingTodos->map(function ($todo) {
                return [
                    'id' => $todo->id,
                    'title' => $todo->title,
                    'description' => $todo->description,
                    'completed' => $todo->completed,
                ];
            }),
            'buttonLabel' => Todo::getAddButtonLabel($date),
        ]);
    }

    /**
     * Store todos for a specific date
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'date' => ['required', 'date'],
            'todos' => ['required', 'array', 'min:1'],
            'todos.*.title' => ['required', 'string', 'max:255'],
            'todos.*.description' => ['nullable', 'string', 'max:1000'],
        ], [
            'date.required' => 'La fecha es requerida.',
            'todos.required' => 'Debe agregar al menos un todo.',
            'todos.min' => 'Debe agregar al menos un todo.',
            'todos.*.title.required' => 'El título del todo es requerido.',
            'todos.*.title.max' => 'El título no puede exceder 255 caracteres.',
            'todos.*.description.max' => 'La descripción no puede exceder 1000 caracteres.',
        ]);

        $date = Carbon::parse($validated['date'])->format('Y-m-d');

        // Check if todos can be edited for this date
        if (!Todo::canEditTodosForDate($date)) {
            return back()->withErrors([
                'date' => 'No se pueden crear o editar todos para esta fecha. Los todos deben crearse antes de las 2 AM del día correspondiente.'
            ])->withInput();
        }

        // Delete existing todos for this date
        Todo::where('user_id', auth()->user()->id)
            ->whereDate('date', $date)
            ->delete();

        // Create new todos
        foreach ($validated['todos'] as $todoData) {
            Todo::create([
                'user_id' => auth()->user()->id,
                'date' => $date,
                'title' => $todoData['title'],
                'description' => $todoData['description'] ?? null,
                'completed' => false,
            ]);
        }

        return redirect()->route('todos.index')->with('success', 'Todos creados exitosamente.');
    }

    /**
     * Show the form for editing todos for a specific date
     */
    public function edit(Request $request, $date)
    {
        $date = Carbon::parse($date)->format('Y-m-d');

        // Check if todos can be edited for this date
        if (!Todo::canEditTodosForDate($date)) {
            return redirect()->route('todos.index')->withErrors([
                'date' => 'No se pueden crear o editar todos para esta fecha. Los todos deben crearse antes de las 2 AM del día correspondiente.'
            ]);
        }

        // Get existing todos for this date
        $existingTodos = Todo::where('user_id', auth()->user()->id)
            ->whereDate('date', $date)
            ->orderBy('completed')
            ->orderBy('created_at')
            ->get();

        return Inertia::render('Todo/Edit', [
            'date' => $date,
            'existingTodos' => $existingTodos->map(function ($todo) {
                return [
                    'id' => $todo->id,
                    'title' => $todo->title,
                    'description' => $todo->description,
                    'completed' => $todo->completed,
                ];
            }),
            'buttonLabel' => Todo::getAddButtonLabel($date),
        ]);
    }

    /**
     * Update todos for a specific date
     */
    public function update(Request $request, $date)
    {
        $date = Carbon::parse($date)->format('Y-m-d');

        $validated = $request->validate([
            'todos' => ['required', 'array', 'min:1'],
            'todos.*.title' => ['required', 'string', 'max:255'],
            'todos.*.description' => ['nullable', 'string', 'max:1000'],
        ], [
            'todos.required' => 'Debe agregar al menos un todo.',
            'todos.min' => 'Debe agregar al menos un todo.',
            'todos.*.title.required' => 'El título del todo es requerido.',
            'todos.*.title.max' => 'El título no puede exceder 255 caracteres.',
            'todos.*.description.max' => 'La descripción no puede exceder 1000 caracteres.',
        ]);

        // Check if todos can be edited for this date
        if (!Todo::canEditTodosForDate($date)) {
            return back()->withErrors([
                'date' => 'No se pueden crear o editar todos para esta fecha. Los todos deben crearse antes de las 2 AM del día correspondiente.'
            ])->withInput();
        }

        // Get existing todos IDs to preserve completed status
        $existingTodos = Todo::where('user_id', auth()->user()->id)
            ->whereDate('date', $date)
            ->get()
            ->keyBy('id');

        $existingIds = $existingTodos->keys()->toArray();
        $newIds = [];

        // Update or create todos
        foreach ($validated['todos'] as $index => $todoData) {
            if (isset($todoData['id']) && isset($existingTodos[$todoData['id']])) {
                // Update existing todo
                $todo = $existingTodos[$todoData['id']];
                $todo->update([
                    'title' => $todoData['title'],
                    'description' => $todoData['description'] ?? null,
                ]);
                $newIds[] = $todo->id;
            } else {
                // Create new todo
                $todo = Todo::create([
                    'user_id' => auth()->user()->id,
                    'date' => $date,
                    'title' => $todoData['title'],
                    'description' => $todoData['description'] ?? null,
                    'completed' => false,
                ]);
                $newIds[] = $todo->id;
            }
        }

        // Delete todos that were removed
        $idsToDelete = array_diff($existingIds, $newIds);
        if (!empty($idsToDelete)) {
            Todo::whereIn('id', $idsToDelete)->delete();
        }

        return redirect()->route('todos.index')->with('success', 'Todos actualizados exitosamente.');
    }

    /**
     * Toggle completion status of a todo
     */
    public function toggleComplete(Request $request, $id)
    {
        $todo = Todo::where('user_id', auth()->user()->id)->findOrFail($id);

        $validated = $request->validate([
            'completed' => ['required', 'boolean'],
        ]);

        $todo->update([
            'completed' => $validated['completed'],
            'completed_at' => $validated['completed'] ? now() : null,
        ]);

        return response()->json([
            'success' => true,
            'todo' => [
                'id' => $todo->id,
                'completed' => $todo->completed,
                'completed_at' => $todo->completed_at ? $todo->completed_at->toISOString() : null,
            ],
        ]);
    }

    /**
     * Remove the specified todo
     */
    public function destroy($id)
    {
        $todo = Todo::where('user_id', auth()->user()->id)->findOrFail($id);
        
        // Check if todos can be edited for this date
        if (!Todo::canEditTodosForDate($todo->date)) {
            return redirect()->route('todos.index')->withErrors([
                'date' => 'No se pueden eliminar todos para esta fecha. Los todos solo pueden eliminarse antes de las 2 AM del día correspondiente.'
            ]);
        }

        $todo->delete();

        return redirect()->back()->with('success', 'Todo eliminado exitosamente.');
    }
}
