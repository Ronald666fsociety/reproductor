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
        $categories = Category::withCount('videos')->orderBy('order')->get();
        
        $unlockedCategories = session('unlocked_categories', []);

        return Inertia::render('Categories/Index', [
            'categories' => $categories->map(function ($category) use ($unlockedCategories) {
                return [
                    'id' => $category->id,
                    'name' => $category->name,
                    'slug' => $category->slug,
                    'videos_count' => $category->videos_count,
                    'is_locked' => !empty($category->password) && !in_array($category->id, $unlockedCategories),
                ];
            }),
        ]);
    }

    public function show(Category $category)
    {
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
        $request->validate([
            'password' => 'required|string',
        ]);

        if (Hash::check($request->password, $category->password)) {
            $unlocked = session('unlocked_categories', []);
            $unlocked[] = $category->id;
            session(['unlocked_categories' => array_unique($unlocked)]);

            return back()->with('success', 'Sección desbloqueada.');
        }

        return back()->withErrors(['password' => 'Contraseña incorrecta.']);
    }
}
