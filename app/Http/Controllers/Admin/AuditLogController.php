<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\ActivityLog;
use App\Models\User;
use Illuminate\Http\Request;
use Inertia\Inertia;

class AuditLogController extends Controller
{
    public function index(Request $request)
    {
        if (!auth()->user()->is_admin) {
            abort(403, 'Acceso denegado.');
        }

        $query = ActivityLog::with('user')->latest();

        // Filters
        if ($request->filled('user_id')) {
            $query->where('user_id', $request->user_id);
        }

        if ($request->filled('action')) {
            $query->where('action', $request->action);
        }

        if ($request->filled('search')) {
            $query->where('description', 'like', '%' . $request->search . '%');
        }

        $logs = $query->paginate(20)->withQueryString();
        $users = User::select('id', 'name')->get();

        return Inertia::render('Admin/AuditLogs', [
            'logs' => $logs,
            'users' => $users,
            'filters' => $request->only(['user_id', 'action', 'search']),
        ]);
    }
}
