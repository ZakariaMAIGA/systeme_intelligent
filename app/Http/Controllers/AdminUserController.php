<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;

class AdminUserController extends Controller
{
    private const ROLES = [
        'patient',
        'agent_accueil',
        'personnel_medical',
        'responsable',
        'admin',
    ];

    public function index()
    {
        $users = User::orderBy('name')->get();

        return view('admin.users.index', compact('users'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'string', 'lowercase', 'email', 'max:255', 'unique:users,email'],
            'password' => ['required', 'confirmed', 'string', 'min:8'],
            'role' => ['required', 'in:' . implode(',', self::ROLES)],
        ]);

        User::create($validated);

        return redirect()->route('admin.users.index')->with('notification', [
            'text' => 'Utilisateur créé avec succès.',
            'type' => 'success',
        ]);
    }

    public function update(Request $request, User $user)
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'string', 'lowercase', 'email', 'max:255', 'unique:users,email,' . $user->id],
            'password' => ['nullable', 'confirmed', 'string', 'min:8'],
            'role' => ['required', 'in:' . implode(',', self::ROLES)],
        ]);

        if ($user->is(auth()->user()) && $validated['role'] !== 'admin') {
            return back()->withErrors(['role' => 'Vous ne pouvez pas retirer votre propre rôle administrateur.']);
        }

        if (blank($validated['password'])) {
            unset($validated['password']);
        }

        $user->update($validated);

        return redirect()->route('admin.users.index')->with('notification', [
            'text' => 'Utilisateur modifié avec succès.',
            'type' => 'success',
        ]);
    }

    public function destroy(User $user)
    {
        if ($user->is(auth()->user())) {
            return back()->withErrors(['user' => 'Vous ne pouvez pas supprimer votre propre compte.']);
        }

        $user->delete();

        return redirect()->route('admin.users.index')->with('notification', [
            'text' => 'Utilisateur supprimé avec succès.',
            'type' => 'success',
        ]);
    }
}
