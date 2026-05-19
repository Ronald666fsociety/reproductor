<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

use App\Models\Category;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

class AdminCategoryController extends Controller
{
    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'password' => 'nullable|string|min:4',
            'order' => 'integer',
        ]);

        Category::create([
            'user_id' => auth()->id(),
            'name' => $validated['name'],
            'slug' => Str::slug($validated['name']) . '-' . Str::random(5),
            'password' => $validated['password'] ? Hash::make($validated['password']) : null,
            'order' => $validated['order'] ?? 0,
        ]);

        return back()->with('success', 'Sección creada correctamente.');
    }

    public function storeNested(Request $request, Category $category)
    {
        if ($category->user_id !== auth()->id()) {
            abort(403);
        }

        $validated = $request->validate([
            'name' => 'required|string|max:255',
        ]);

        Category::create([
            'user_id' => auth()->id(),
            'parent_id' => $category->id,
            'name' => $validated['name'],
            'slug' => Str::slug($validated['name']) . '-' . Str::random(5),
            'order' => $category->children()->count() + 1,
        ]);

        return back()->with('success', 'Subdirectorio creado correctamente.');
    }

    public function update(Request $request, Category $category)
    {
        if ($category->user_id !== auth()->id()) {
            abort(403, 'Acceso denegado.');
        }

        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'password' => 'nullable|string|min:4',
            'order' => 'integer',
        ]);

        $category->update([
            'name' => $validated['name'],
            'slug' => Str::slug($validated['name']),
            'order' => $validated['order'] ?? 0,
        ]);

        if ($validated['password']) {
            $category->password = Hash::make($validated['password']);
            $category->save();
        }

        return back()->with('success', 'Sección actualizada.');
    }

    public function destroy(Category $category)
    {
        if ($category->user_id !== auth()->id()) {
            abort(403, 'Acceso denegado.');
        }

        $category->delete();
        return back()->with('success', 'Sección eliminada.');
    }
}
