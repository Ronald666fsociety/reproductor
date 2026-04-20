<?php

namespace App\Http\Controllers;

use App\Models\ShareLink;
use App\Models\Video;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Inertia\Inertia;

class ShareController extends Controller
{
    public function create(Request $request, Video $video)
    {
        // Solo el dueño puede crear links de compartir
        if ($video->category->user_id !== auth()->id()) {
            abort(403);
        }

        $token = Str::random(40);
        
        $shareLink = ShareLink::create([
            'video_id' => $video->id,
            'token' => $token,
            'expires_at' => now()->addHours(24),
        ]);

        return response()->json([
            'link' => route('share.show', $token),
            'expires_at' => $shareLink->expires_at->toDateTimeString()
        ]);
    }

    public function show($token)
    {
        $shareLink = ShareLink::where('token', $token)
            ->where('expires_at', '>', now())
            ->with(['video', 'video.category'])
            ->firstOrFail();

        return Inertia::render('Public/ShareView', [
            'video' => $shareLink->video,
            'category_name' => $shareLink->video->category->name
        ]);
    }
}
