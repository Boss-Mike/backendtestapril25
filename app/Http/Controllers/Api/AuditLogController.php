<?php

namespace App\Http\Controllers\Api;

use App\Models\AuditLog;
use Illuminate\Http\Request;

class AuditLogController
{
    public function index(Request $request)
    {
        // Only admins and managers can view audit logs
        if (!in_array($request->user()->role, ['Admin', 'Manager'])) {
            return response()->json(['message' => 'Unauthorized'], 403);
        }

        $logs = AuditLog::where('company_id', $request->user()->company_id)
            ->with(['user', 'expense'])
            ->orderBy('created_at', 'desc')
            ->paginate(20);

        return response()->json($logs);
    }

    public function show(Request $request, AuditLog $log)
    {
        if ($log->company_id !== $request->user()->company_id) {
            return response()->json(['message' => 'Unauthorized'], 403);
        }

        if (!in_array($request->user()->role, ['Admin', 'Manager'])) {
            return response()->json(['message' => 'Unauthorized'], 403);
        }

        return response()->json($log->load(['user', 'expense']));
    }
}
