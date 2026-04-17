<?php

namespace App\Http\Controllers;

use App\Models\Category;
use App\Models\Video;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Inertia\Inertia;
use Illuminate\Support\Str;

class VideoController extends Controller
{
    public function upload(Request $request)
    {
        $request->validate([
            'video' => 'required|mimes:mp4,mov,ogg,qt|max:2097152', // 2GB limit
            'category_id' => 'required|exists:categories,id',
            'title' => 'required|string|max:255',
        ]);

        if ($request->hasFile('video')) {
            $path = $request->file('video')->store('videos', 'public');
            
            // Generar miniatura con FFmpeg
            $thumbnailPath = 'thumbnails/' . Str::random(40) . '.jpg';
            $fullVideoPath = storage_path('app/public/' . $path);
            $fullThumbnailPath = storage_path('app/public/' . $thumbnailPath);
            
            if (!file_exists(storage_path('app/public/thumbnails'))) {
                mkdir(storage_path('app/public/thumbnails'), 0755, true);
            }

            // Comando FFmpeg para extraer el frame del segundo 1
            $command = "ffmpeg -i \"$fullVideoPath\" -ss 00:00:01.000 -vframes 1 \"$fullThumbnailPath\" 2>&1";
            exec($command, $output, $returnVar);

            // Si falla FFmpeg, podemos registrar el error pero seguir guardando el video
            $finalThumbnailPath = ($returnVar === 0 && file_exists($fullThumbnailPath)) ? $thumbnailPath : null;

            // Guardar en la base de datos
            Video::create([
                'category_id' => $request->category_id,
                'title' => $request->title,
                'path' => $path,
                'thumbnail_path' => $finalThumbnailPath,
                'order' => Video::where('category_id', $request->category_id)->count() + 1,
            ]);

            return back()->with('success', 'Video subido y procesado con éxito.');
        }

        return back()->with('error', 'Error al subir el video.');
    }

    public function destroy(Video $video)
    {
        Storage::disk('public')->delete($video->path);
        if ($video->thumbnail_path) {
            Storage::disk('public')->delete($video->thumbnail_path);
        }
        $video->delete();
        
        return back()->with('success', 'Video eliminado.');
    }
}
