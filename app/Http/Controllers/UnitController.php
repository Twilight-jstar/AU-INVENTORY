<?php

namespace App\Http\Controllers;

use App\Models\Unit;
use Illuminate\Http\Request;
use Inertia\Inertia;

class UnitController extends Controller
{
    public function index()
    {
        return Inertia::render('Units/Index', [
            'units' => Unit::withCount('items')->get() // Added item count for better UI
        ]);
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|max:50|unique:units,name' // Added unique check
        ]);

        Unit::create($validated);
        
        return redirect()->route('units.index')->with('message', 'Unit created.');
    }

    public function update(Request $request, Unit $unit)
    {
        $validated = $request->validate([
            'name' => 'required|max:50|unique:units,name,' . $unit->id
        ]);

        $unit->update($validated);
        
        return redirect()->route('units.index')->with('message', 'Unit updated.');
    }

    public function destroy(Unit $unit)
    {
        // Safety Check: Don't delete if items are using this unit
        if ($unit->items()->count() > 0) {
            return redirect()->back()->with('error', 'Cannot delete unit. It is currently linked to items.');
        }

        $unit->delete();
        
        return redirect()->route('units.index')->with('message', 'Unit deleted.');
    }
}