<?php

namespace App\Http\Controllers;

use App\Models\AuditLog;
use Illuminate\Http\Request;
use Illuminate\View\View;

class AuditLogController extends Controller
{
    public function index(Request $request): View
    {
        $query = AuditLog::with('user');

        if ($module = $request->input('module')) {
            $query->where('module', $module);
        }

        if ($action = $request->input('action')) {
            $query->where('action', $action);
        }

        if ($search = $request->input('search')) {
            $query->where(function ($q) use ($search) {
                $q->where('description', 'like', "%{$search}%")
                    ->orWhere('ip_address', 'like', "%{$search}%")
                    ->orWhere('record_id', 'like', "%{$search}%");
            });
        }

        $logs = $query->orderBy('created_at', 'desc')->paginate(20)->withQueryString();
        $modules = AuditLog::distinct()->pluck('module')->filter();

        return view('admin.audit-logs.index', compact('logs', 'modules'));
    }
}
