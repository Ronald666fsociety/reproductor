<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\ActivityLog;
use App\Models\User;
use App\Models\Video;
use Illuminate\Support\Facades\DB;
use Inertia\Inertia;

class DashboardController extends Controller
{
    public function index()
    {
        $totalStorage = Video::withTrashed()->sum('file_size');
        $totalFiles = Video::withTrashed()->count();
        $totalUsers = User::count();

        // Get storage by type (Database agnostic)
        $storageByTypeRaw = Video::withTrashed()
            ->selectRaw('sum(file_size) as total, file_type')
            ->groupBy('file_type')
            ->get();

        $storageByType = [
            'video' => 0,
            'image' => 0,
            'application' => 0, // Usually PDFs
            'audio' => 0,
            'other' => 0,
        ];

        foreach ($storageByTypeRaw as $item) {
            // Extraer la primera parte del mime type (ej. video/mp4 -> video)
            $mainType = explode('/', $item->file_type)[0] ?? 'other';
            $type = strtolower($mainType);
            
            if (array_key_exists($type, $storageByType)) {
                $storageByType[$type] += (float)$item->total;
            } else {
                $storageByType['other'] += (float)$item->total;
            }
        }

        $recentActivity = ActivityLog::with('user')
            ->latest()
            ->take(10)
            ->get();

        // Top users by storage
        $topUsers = DB::table('users')
            ->leftJoin('categories', 'users.id', '=', 'categories.user_id')
            ->leftJoin('videos', 'categories.id', '=', 'videos.category_id')
            ->select('users.id', 'users.name', 'users.email', 'users.storage_quota', DB::raw('COALESCE(SUM(videos.file_size), 0) as used_storage'))
            ->groupBy('users.id', 'users.name', 'users.email', 'users.storage_quota')
            ->orderByDesc('used_storage')
            ->limit(5)
            ->get();

        return Inertia::render('Admin/Dashboard', [
            'stats' => [
                'totalStorage' => (float)$totalStorage,
                'totalFiles' => $totalFiles,
                'totalUsers' => $totalUsers,
            ],
            'storageByType' => $storageByType,
            'recentActivity' => $recentActivity,
            'topUsers' => $topUsers
        ]);
    }
}
