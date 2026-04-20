<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Video;
use App\Models\Category;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Illuminate\Support\Facades\Storage;

class TrashController extends Controller
{
    public function index()
    {
        $this->authorizeAdmin();

        $deletedVideos = Video::onlyTrashed()->with('category')->latest('deleted_at')->get();
        $deletedCategories = Category::onlyTrashed()->withCount('videos')->latest('deleted_at')->get();

        return Inertia::render('Admin/Trash', [
            'videos' => $deletedVideos,
            'categories' => $deletedCategories,
        ]);
    }

    public function restoreVideo($id)
    {
        $this->authorizeAdmin();
        $video = Video::onlyTrashed()->findOrFail($id);
        $video->restore();

        return back()->with('success', 'Archivo restaurado correctamente.');
    }

    public function restoreCategory($id)
    {
        $this->authorizeAdmin();
        $category = Category::onlyTrashed()->findOrFail($id);
        $category->restore();

        return back()->with('success', 'Sección restaurada correctamente.');
    }

    public function forceDeleteVideo($id)
    {
        $this->authorizeAdmin();
        $video = Video::onlyTrashed()->findOrFail($id);
        
        // Delete physical files
        Storage::disk('public')->delete($video->path);
        if ($video->thumbnail_path) {
            Storage::disk('public')->delete($video->thumbnail_path);
        }

        $video->forceDelete();

        return back()->with('success', 'Archivo eliminado permanentemente.');
    }

    public function forceDeleteCategory($id)
    {
        $this->authorizeAdmin();
        $category = Category::onlyTrashed()->findOrFail($id);
        
        // Optionally force delete all videos in this category too
        // For simplicity, we just purge the category record. 
        // If it was soft deleted, the files are still there until videos are purged.
        
        $category->forceDelete();

        return back()->with('success', 'Sección eliminada permanentemente.');
    }

    protected function authorizeAdmin()
    {
        if (!auth()->user()->is_admin) {
            abort(403, 'Acceso denegado.');
        }
    }
}
