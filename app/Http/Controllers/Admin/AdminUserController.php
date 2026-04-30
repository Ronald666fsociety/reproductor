<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Inertia\Inertia;
use Illuminate\Validation\Rules;

class AdminUserController extends Controller
{
    public function index()
    {
        // Obtener todos los usuarios excepto el admin principal y ordenar
        $users = User::where('id', '!=', auth()->id())->orderBy('created_at', 'desc')->get();

        return Inertia::render('Admin/Users', [
            'users' => $users->map(function ($user) {
                return [
                    'id' => $user->id,
                    'name' => $user->name,
                    'email' => $user->email,
                    'is_admin' => $user->is_admin,
                    'storage_quota' => $user->storage_quota,
                    'used_storage' => $user->usedStorage(),
                    'created_at' => $user->created_at->format('d/m/Y'),
                ];
            })
        ]);
    }

    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|string|email|max:255|unique:users',
            'password' => ['required', Rules\Password::defaults()],
            'storage_quota' => 'nullable|numeric|min:0', // In bytes
        ]);

        User::create([
            'name' => $request->name,
            'email' => $request->email,
            'password' => Hash::make($request->password),
            'is_admin' => false, // Usuarios creados son normales
            'storage_quota' => $request->storage_quota,
        ]);

        return back()->with('success', 'Usuario creado correctamente.');
    }

    public function update(Request $request, User $user)
    {
        $request->validate([
            'storage_quota' => 'nullable|numeric|min:0',
        ]);

        $user->update([
            'storage_quota' => $request->storage_quota,
        ]);

        return back()->with('success', 'Cuota de almacenamiento actualizada.');
    }

    public function destroy(User $user)
    {
        // No permitir eliminar al mismo admin
        if ($user->id === auth()->id() || $user->is_admin) {
            abort(403, 'No se puede eliminar a este administrador.');
        }

        $user->delete();

        return back()->with('success', 'Usuario eliminado.');
    }
}
