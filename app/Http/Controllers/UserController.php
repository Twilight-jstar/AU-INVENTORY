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
    public function index()
    {
        // Kukunin natin lahat ng users sa database, naka-arrange by name
        $users = User::orderBy('name')->get();

        // Ipapasa natin 'yung data sa 'Users/Index.vue' na gagawin natin mamaya
        return Inertia::render('Users/Index', [
            'users' => $users
        ]);
    }

    /**
     * I-save ang bagong gawang user sa database.
     */
    public function store(Request $request)
    {
        // I-check kung tama ba ang nilagay na data ni Admin
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'username' => 'required|string|max:255|unique:users',
            'email' => 'required|string|email|max:255|unique:users',
            'role' => 'required|string|in:Admin,Custodian,Clerk,Viewer',
            'password' => 'required|string|min:8',
        ]);

        // I-hash (i-encrypt) ang password bago i-save
        $validated['password'] = Hash::make($validated['password']);

        User::create($validated);

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

        // Kung may tinype na bagong password, i-encrypt. Kung wala, wag isama sa update.
        if ($request->filled('password')) {
            $validated['password'] = Hash::make($request->password);
        }

        $user->update($validated);

        return redirect()->route('users.index')->with('success', 'User updated successfully.');
    }

    /**
     * I-delete ang user.
     */
    public function destroy(User $user)
    {
        // Protection: Bawal i-delete ng Admin ang sarili niyang account
        if (auth()->id() === $user->id) {
            return back()->with('error', 'You cannot delete your own account.');
        }

        $user->delete();

        return redirect()->route('users.index')->with('success', 'User deleted successfully.');
    }
}