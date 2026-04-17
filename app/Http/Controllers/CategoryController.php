<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;


use App\Models\Category;
use Illuminate\Support\Facades\Hash;
use Inertia\Inertia;

class CategoryController extends Controller
{
    public function index()
    {
        // Seguridad: Limpiar categorías desbloqueadas al volver al índice
        session()->forget('unlocked_categories');

        $categories = Category::where('user_id', auth()->id())
            ->withCount('videos')
            ->orderBy('order')
            ->get();
        
        return Inertia::render('Categories/Index', [
            'categories' => $categories->map(function ($category) {
                return [
                    'id' => $category->id,
                    'name' => $category->name,
                    'slug' => $category->slug,
                    'videos_count' => $category->videos_count,
                    'is_locked' => !empty($category->password),
                ];
            }),
        ]);
    }

    public function show(Category $category)
    {
        if ($category->user_id !== auth()->id()) {
            abort(403, 'Acceso denegado.');
        }

        $unlockedCategories = session('unlocked_categories', []);

        if (!empty($category->password) && !in_array($category->id, $unlockedCategories)) {
            return back()->with('error', 'Esta sección está protegida.');
        }

        return Inertia::render('Categories/Show', [
            'category' => $category->load('videos'),
        ]);
    }

    public function unlock(Request $request, Category $category)
    {
        if ($category->user_id !== $request->user()->id) {
            abort(403, 'Acceso denegado.');
        }

        $request->validate([
            'password' => 'required|string',
        ]);

        $user = $request->user();
        $isMasterKey = $user && Hash::check($request->password, $user->password);

        if (Hash::check($request->password, $category->password) || $isMasterKey) {
            $unlocked = session('unlocked_categories', []);
            $unlocked[] = $category->id;
            session(['unlocked_categories' => array_unique($unlocked)]);

            return redirect()->route('categories.show', $category->id)->with('success', $isMasterKey ? 'Sección desbloqueada con Llave Maestra.' : 'Sección desbloqueada.');
        }

        return back()->withErrors(['password' => 'Contraseña incorrecta.']);
    }
}
