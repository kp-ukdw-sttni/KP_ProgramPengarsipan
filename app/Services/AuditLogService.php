<?php

namespace App\Services;

use App\Models\AuditLog;
use App\Models\User;
use Illuminate\Http\Request;

class AuditLogService
{
    /**
     * Build the audit log listing data with filters.
     */
    public function getIndexData(Request $request, User $user): array
    {
        $query = AuditLog::with(['user', 'arsip']);

        // Filter logs based on user role
        if ($user->hasRole('Operator') || $user->hasRole('Staf TU')) {
            $divisiId = $user->divisi_id;
            $query->where(function ($q) use ($divisiId) {
                // logs relating to archives of operator's division
                $q->whereHas('arsip', function ($q2) use ($divisiId) {
                    $q2->where('divisi_id', $divisiId);
                })
                // OR logs performed by users of operator's division
                    ->orWhereHas('user', function ($q2) use ($divisiId) {
                        $q2->where('divisi_id', $divisiId);
                    });
            });

            // Operators can only filter by users in their own division
            $users = User::where('divisi_id', $divisiId)->orderBy('name')->get();
        } else {
            // Superadmins can filter by all users
            $users = User::orderBy('name')->get();
        }

        // Filter by user
        if ($request->filled('user_id')) {
            $query->where('user_id', $request->user_id);
        }

        // Filter by action type
        if ($request->filled('action')) {
            $query->where('action', $request->action);
        }

        // Filter by date range
        if ($request->filled('start_date')) {
            $query->whereDate('created_at', '>=', $request->start_date);
        }
        if ($request->filled('end_date')) {
            $query->whereDate('created_at', '<=', $request->end_date);
        }

        $logs = $query->latest('id')->paginate(20)->withQueryString();

        return [
            'logs' => $logs,
            'users' => $users,
            'filters' => $request->only(['user_id', 'action', 'start_date', 'end_date']),
        ];
    }
}
