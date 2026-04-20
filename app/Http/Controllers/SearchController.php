<?php

namespace App\Http\Controllers;

use App\Models\Category;
use App\Models\Video;
use Illuminate\Http\Request;

class SearchController extends Controller
{
    public function search(Request $request)
    {
        $query = $request->input('q');

        if (empty($query)) {
            return response()->json(['categories' => [], 'files' => []]);
        }

        $categories = Category::where('user_id', auth()->id())
            ->where('name', 'like', "%{$query}%")
            ->take(5)
            ->get(['id', 'name', 'slug']);

        $files = Video::whereHas('category', function($q) {
                $q->where('user_id', auth()->id());
            })
            ->where(function($q) use ($query) {
                $q->where('title', 'like', "%{$query}%")
                  ->orWhere('description', 'like', "%{$query}%");
            })
            ->with('category:id,name')
            ->take(10)
            ->get(['id', 'title', 'category_id', 'file_type', 'thumbnail_path']);

        return response()->json([
            'categories' => $categories,
            'files' => $files
        ]);
    }
}
