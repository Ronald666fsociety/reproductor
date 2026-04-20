<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;


use App\Models\Category;
use App\Models\Video;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Inertia\Inertia;
use ZipArchive;

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
        
        $totalUsed = Video::whereHas('category', function($q) {
                $q->where('user_id', auth()->id());
            })->sum('file_size');

        $diskTotal = @disk_total_space(base_path()) ?: 0;
        $diskFree = @disk_free_space(base_path()) ?: 0;
        $fileTypes = Video::whereHas('category', function($q) {
                $q->where('user_id', auth()->id());
            })
            ->select('file_type', DB::raw('count(*) as count'), DB::raw('sum(file_size) as total_size'))
            ->groupBy('file_type')
            ->get();

        $deletedCount = Video::onlyTrashed()->whereHas('category', function($q) {
                $q->where('user_id', auth()->id());
            })->count() + Category::onlyTrashed()->where('user_id', auth()->id())->count();

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
            'stats' => [
                'total_used' => (int)$totalUsed,
                'disk_total' => (int)$diskTotal,
                'disk_free' => (int)$diskFree,
                'file_types' => $fileTypes,
                'deleted_count' => $deletedCount
            ]
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
    public function downloadZip(Category $category)
    {
        if ($category->user_id !== auth()->id()) {
            abort(403);
        }

        $videos = $category->videos;
        if ($videos->isEmpty()) {
            return back()->with('error', 'La sección está vacía.');
        }

        $zipName = $category->slug . '_' . time() . '.zip';
        $zipPath = storage_path('app/public/' . $zipName);

        $zip = new ZipArchive;
        if ($zip->open($zipPath, ZipArchive::CREATE) === TRUE) {
            foreach ($videos as $video) {
                $filePath = storage_path('app/public/' . $video->path);
                if (file_exists($filePath)) {
                    $zip->addFile($filePath, $video->title . '.' . pathinfo($video->path, PATHINFO_EXTENSION));
                }
            }
            $zip->close();
        }

        return response()->download($zipPath)->deleteFileAfterSend(true);
    }
}
