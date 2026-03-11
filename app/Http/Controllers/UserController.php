<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rule;
use Inertia\Inertia;

class UserController extends Controller
{
    /**
     * Ipakita ang listahan ng lahat ng users.
     */
    public function index(Request $request)
    {
        $users = User::orderBy('name')->get();

        if ($request->wantsJson()) {
            return response()->json($users);
        }

        return Inertia::render('Users/Index', [
            'users' => $users
        ]);
    }

    /**
     * I-save ang bagong gawang user sa database.
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'username' => 'required|string|max:255|unique:users',
            'email' => 'required|string|email|max:255|unique:users',
            'role' => 'required|string|in:Admin,Custodian,Clerk,Viewer',
            'password' => 'required|string|min:8',
        ]);

        $validated['password'] = Hash::make($validated['password']);

        $user = User::create($validated);

        if ($request->wantsJson()) {
            return response()->json([
                'message' => 'User created successfully.',
                'user' => $user
            ], 201);
        }

        return redirect()->route('users.index')->with('success', 'User created successfully.');
    }

    /**
     * I-update ang information ng existing user.
     */
    public function update(Request $request, User $user)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'username' => ['required', 'string', 'max:255', Rule::unique('users')->ignore($user->id)],
            'email' => ['required', 'string', 'email', 'max:255', Rule::unique('users')->ignore($user->id)],
            'role' => 'required|string|in:Admin,Custodian,Clerk,Viewer',
        ]);

        if ($request->filled('password')) {
            $validated['password'] = Hash::make($request->password);
        }

        $user->update($validated);

        if ($request->wantsJson()) {
            return response()->json([
                'message' => 'User updated successfully.',
                'user' => $user
            ]);
        }

        return redirect()->route('users.index')->with('success', 'User updated successfully.');
    }

    /**
     * I-delete ang user.
     */
    public function destroy(Request $request, User $user)
    {
        if (auth()->id() === $user->id) {
            if ($request->wantsJson()) {
                return response()->json(['error' => 'You cannot delete your own account.'], 403);
            }
            return back()->with('error', 'You cannot delete your own account.');
        }

        $user->delete();

        if ($request->wantsJson()) {
            return response()->json(['message' => 'User deleted successfully.']);
        }

        return redirect()->route('users.index')->with('success', 'User deleted successfully.');
    }
}