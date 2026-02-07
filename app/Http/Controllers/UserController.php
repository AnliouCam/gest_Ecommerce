<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Rules\Password;

class UserController extends Controller
{
    /**
     * Display a listing of users.
     */
    public function index(Request $request)
    {
        $query = User::withCount(['sales', 'stockAdjustments']);

        if ($request->filled('role')) {
            $query->where('role', $request->role);
        }

        if ($request->filled('search')) {
            $search = str_replace(['%', '_'], ['\\%', '\\_'], $request->search);
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                  ->orWhere('email', 'like', "%{$search}%");
            });
        }

        $users = $query->orderBy('name')->paginate(15)->withQueryString();

        return view('users.index', compact('users'));
    }

    /**
     * Show the form for creating a new user.
     */
    public function create()
    {
        return view('users.create');
    }

    /**
     * Store a newly created user.
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'email', 'max:255', 'unique:users,email'],
            'password' => ['required', 'string', 'min:8', 'confirmed'],
            'role' => ['required', Rule::in(['gerant', 'vendeur'])],
        ]);

        $validated['password'] = Hash::make($validated['password']);

        User::create($validated);

        return redirect()->route('gerant.users.index')
            ->with('success', 'Utilisateur cree avec succes.');
    }

    /**
     * Display the specified user.
     */
    public function show(User $user)
    {
        $user->loadCount(['sales', 'stockAdjustments', 'cancelledSales']);

        $recentSales = $user->sales()
            ->with('customer:id,name')
            ->orderByDesc('created_at')
            ->limit(5)
            ->get();

        return view('users.show', compact('user', 'recentSales'));
    }

    /**
     * Show the form for editing the specified user.
     */
    public function edit(User $user)
    {
        return view('users.edit', compact('user'));
    }

    /**
     * Update the specified user.
     */
    public function update(Request $request, User $user)
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'email', 'max:255', Rule::unique('users')->ignore($user->id)],
            'password' => ['nullable', 'string', 'min:8', 'confirmed'],
            'role' => ['required', Rule::in(['gerant', 'vendeur'])],
        ]);

        if ($user->isGerant() && $validated['role'] === 'vendeur') {
            $gerantCount = User::where('role', 'gerant')->count();
            if ($gerantCount <= 1) {
                return back()->with('error', 'Impossible de changer le role. Il doit y avoir au moins un gerant.');
            }
        }

        if (!empty($validated['password'])) {
            $validated['password'] = Hash::make($validated['password']);
        } else {
            unset($validated['password']);
        }

        $user->update($validated);

        return redirect()->route('gerant.users.index')
            ->with('success', 'Utilisateur mis a jour avec succes.');
    }

    /**
     * Remove the specified user.
     */
    public function destroy(Request $request, User $user)
    {
        if ($user->id === $request->user()->id) {
            return back()->with('error', 'Vous ne pouvez pas supprimer votre propre compte.');
        }

        if ($user->isGerant()) {
            $gerantCount = User::where('role', 'gerant')->count();
            if ($gerantCount <= 1) {
                return back()->with('error', 'Impossible de supprimer le dernier gerant.');
            }
        }

        if ($user->sales()->exists()) {
            return back()->with('error', 'Impossible de supprimer cet utilisateur car il a des ventes associees.');
        }

        $user->delete();

        return redirect()->route('gerant.users.index')
            ->with('success', 'Utilisateur supprime avec succes.');
    }
}
