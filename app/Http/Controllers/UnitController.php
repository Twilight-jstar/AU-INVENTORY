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
            'units' => Unit::all()
        ]);
    }

    public function store(Request $request)
    {
        $request->validate(['name' => 'required|max:50']);
        Unit::create($request->all());
        return redirect()->route('units.index');
    }
}
