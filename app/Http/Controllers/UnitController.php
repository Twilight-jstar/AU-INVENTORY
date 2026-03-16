<?php

namespace App\Http\Controllers;

use App\Models\Unit;
use Illuminate\Http\Request;
use Inertia\Inertia;

class UnitController extends Controller
{
    public function index(Request $request)
    {
        $units = Unit::withCount('items')->get();

        if ($request->wantsJson()) {
            return response()->json($units);
        }

        return Inertia::render('Units/Index', [
            'units' => $units
        ]);
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|max:50|unique:units,name'
        ]);

        $unit = Unit::create($validated);
        
        if ($request->wantsJson()) {
            return response()->json([
                'message' => 'Unit created.',
                'unit' => $unit
            ], 201);
        }

        return redirect()->route('units.index')->with('message', 'Unit created.');
    }

    public function update(Request $request, Unit $unit)
    {
        $validated = $request->validate([
            'name' => 'required|max:50|unique:units,name,' . $unit->id
        ]);

        $unit->update($validated);
        
        if ($request->wantsJson()) {
            return response()->json([
                'message' => 'Unit updated.',
                'unit' => $unit
            ]);
        }

        return redirect()->route('units.index')->with('message', 'Unit updated.');
    }

    public function destroy(Request $request, Unit $unit)
    {
        if ($unit->items()->count() > 0) {
            if ($request->wantsJson()) {
                return response()->json([
                    'error' => 'Cannot delete unit. It is currently linked to items.'
                ], 422);
            }
            return redirect()->back()->with('error', 'Cannot delete unit. It is currently linked to items.');
        }

        $unit->delete();
        
        if ($request->wantsJson()) {
            return response()->json(['message' => 'Unit deleted.']);
        }

        return redirect()->route('units.index')->with('message', 'Unit deleted.');
    }
}