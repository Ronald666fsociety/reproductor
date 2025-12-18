<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Inertia\Inertia;

class VideoController extends Controller
{
    public function index()
    {
        $videos = [];
        $files = Storage::disk('public')->files('videos');

        foreach ($files as $file) {
            $videos[] = [
                'name' => basename($file),
                'url' => Storage::url($file),
            ];
        }

        return Inertia::render('Dashboard', [
            'videos' => $videos,
        ]);
    }

    public function upload(Request $request)
    {
        $request->validate([
            'video' => 'required|mimes:mp4,mov,ogg,qt|max:2097152', // 2GB limit
        ]);

        if ($request->hasFile('video')) {
            $path = $request->file('video')->store('videos', 'public');
            return back()->with('success', 'Video uploaded successfully!');
        }

        return back()->with('error', 'Upload failed.');
    }
}
