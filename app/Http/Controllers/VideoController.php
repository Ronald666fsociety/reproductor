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
            'video' => 'required|max:2097152',
            'category_id' => 'required|exists:categories,id',
            'title' => 'required|string|max:255',
            'tags' => 'nullable|string', // Comma separated tags
        ]);

        $category = Category::findOrFail($request->category_id);
        $user = auth()->user();

        if ($category->user_id !== $user->id) {
            abort(403, 'Acceso denegado.');
        }

        if ($request->hasFile('video')) {
            $file = $request->file('video');

            // Quota check
            if ($user->storage_quota !== null) {
                $usedStorage = $user->usedStorage();
                if (($usedStorage + $file->getSize()) > $user->storage_quota) {
                    return back()->with('error', 'No tienes suficiente espacio de almacenamiento disponible.');
                }
            }

            $mimeType = $file->getMimeType();
            $path = $file->store('videos', 'public');
            
            $finalThumbnailPath = null;
            
            if (str_starts_with($mimeType, 'video/')) {
                // Generar miniatura con FFmpeg
                $thumbnailPath = 'thumbnails/' . Str::random(40) . '.jpg';
                $fullVideoPath = storage_path('app/public/' . $path);
                $fullThumbnailPath = storage_path('app/public/' . $thumbnailPath);
                
                if (!file_exists(storage_path('app/public/thumbnails'))) {
                    mkdir(storage_path('app/public/thumbnails'), 0755, true);
                }

                $command = "ffmpeg -i \"$fullVideoPath\" -ss 00:00:01.000 -vframes 1 \"$fullThumbnailPath\" 2>&1";
                exec($command, $output, $returnVar);

                $finalThumbnailPath = ($returnVar === 0 && file_exists($fullThumbnailPath)) ? $thumbnailPath : null;
            } elseif (str_starts_with($mimeType, 'image/')) {
                // Para las imágenes, la foto en sí es la miniatura
                $finalThumbnailPath = $path;
            }

            // Guardar en la base de datos
            $video = Video::create([
                'category_id' => $request->category_id,
                'title' => $request->title,
                'description' => $request->description ?? null,
                'path' => $path,
                'thumbnail_path' => $finalThumbnailPath,
                'file_type' => $mimeType,
                'file_size' => $file->getSize(),
                'order' => Video::where('category_id', $request->category_id)->withoutGlobalScopes()->count() + 1,
            ]);

            // Manejar etiquetas
            if ($request->filled('tags')) {
                $tagNames = explode(',', $request->tags);
                foreach ($tagNames as $name) {
                    $name = trim($name);
                    if ($name) {
                        $tag = \App\Models\Tag::firstOrCreate(['name' => $name]);
                        $video->tags()->attach($tag->id);
                    }
                }
            }

            return back()->with('success', 'Archivo subido y procesado con éxito.');
        }

        return back()->with('error', 'Error al subir el archivo.');
    }

    public function stream(Video $video)
    {
        // Protección: Solo el dueño de la categoría o el admin mundial puede ver el archivo
        if ($video->category->user_id !== auth()->id() && !auth()->user()->is_admin) {
            abort(403, 'Acceso denegado.');
        }

        $path = storage_path('app/public/' . $video->path);
        
        if (!file_exists($path)) {
            abort(404, 'El archivo no existe.');
        }

        // response()->file() de Symfony maneja autómaticamente cabeceras de rango (HTTP 206)
        // Esto es la magia que permite que la barra de tiempo funcione al adelantar.
        return response()->file($path);
    }

    public function destroy(Video $video)
    {
        if ($video->category->user_id !== auth()->id() && !auth()->user()->is_admin) {
            abort(403, 'Acceso denegado.');
        }

        // Now we just soft delete. Files are kept until purged from Trash.
        $video->delete();
        
        return back()->with('success', 'Archivo movido a la papelera.');
    }

    public function toggleFavorite(Video $video)
    {
        if ($video->category->user_id !== auth()->id() && !auth()->user()->is_admin) {
            abort(403, 'Acceso denegado.');
        }

        $video->update(['is_favorite' => !$video->is_favorite]);

        return back();
    }
}
