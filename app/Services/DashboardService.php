<?php

namespace App\Services;

use App\Models\Arsip;
use App\Models\AuditLog;
use App\Models\Divisi;
use App\Models\Peminjaman;
use App\Models\User;

class DashboardService
{
    /**
     * Build the dashboard stats and activity data based on the user's role.
     */
    public function getDashboardData(User $user): array
    {
        $totalArsip = 0;
        $totalActivePeminjaman = 0; // Will be mapped to "Downloads/Unduhan" count for analytics
        $totalUsers = 0;
        $totalExpiredArsip = 0;
        $totalPendingPeminjaman = 0;
        $totalCompletedPeminjaman = 0;
        $divisiStats = collect();
        $recentPeminjaman = collect();
        $myActivePeminjaman = collect();

        if ($user->hasRole('Superadmin')) {
            $totalArsip = Arsip::count();
            $totalActivePeminjaman = AuditLog::where('action', 'Download')->count(); // Total Unduhan
            $totalUsers = User::count();
            $totalExpiredArsip = Arsip::where('status', 'Expired')->count();
            $totalPendingPeminjaman = Peminjaman::where('status_approval', 'Pending')->count();
            $totalCompletedPeminjaman = Peminjaman::where('status_approval', 'Approved')
                ->where('expired_at', '<=', now())
                ->count();

            $divisiStats = Divisi::withCount('arsip')->get();

            $recentPeminjaman = Peminjaman::with(['user', 'arsip.divisi', 'arsip.kategori'])
                ->latest()
                ->take(5)
                ->get();
        } elseif ($user->hasRole('Operator')) {
            $divisiId = $user->divisi_id;

            $totalArsip = Arsip::where('divisi_id', $divisiId)->count();
            $totalActivePeminjaman = AuditLog::where('action', 'Download')
                ->whereHas('arsip', function ($query) use ($divisiId) {
                    $query->where('divisi_id', $divisiId);
                })
                ->count();
            $totalUsers = User::where('divisi_id', $divisiId)->count();
            $totalExpiredArsip = Arsip::where('divisi_id', $divisiId)->where('status', 'Expired')->count();
            $totalPendingPeminjaman = Peminjaman::whereHas('arsip', function ($query) use ($divisiId) {
                $query->where('divisi_id', $divisiId);
            })
                ->where('status_approval', 'Pending')
                ->count();
            $totalCompletedPeminjaman = Peminjaman::whereHas('arsip', function ($query) use ($divisiId) {
                $query->where('divisi_id', $divisiId);
            })
                ->where('status_approval', 'Approved')
                ->where('expired_at', '<=', now())
                ->count();

            $recentPeminjaman = Peminjaman::whereHas('arsip', function ($query) use ($divisiId) {
                $query->where('divisi_id', $divisiId);
            })
                ->with(['user', 'arsip.divisi', 'arsip.kategori'])
                ->latest()
                ->take(5)
                ->get();

            $divisiStats = Divisi::where('id', $divisiId)->withCount('arsip')->get();
        } else {
            // Karyawan / Employee
            $totalArsip = Arsip::where('status', 'Aktif')->count();

            $myActivePeminjaman = Peminjaman::where('user_id', $user->id)
                ->where('status_approval', 'Approved')
                ->where('expired_at', '>', now())
                ->with('arsip')
                ->get();

            $totalActivePeminjaman = AuditLog::where('user_id', $user->id)->where('action', 'Download')->count(); // Personal Downloads
            $totalExpiredArsip = Arsip::where('status', 'Expired')->count();
            $totalPendingPeminjaman = Peminjaman::where('user_id', $user->id)
                ->where('status_approval', 'Pending')
                ->count();
            $totalCompletedPeminjaman = Peminjaman::where('user_id', $user->id)
                ->where('status_approval', 'Approved')
                ->where('expired_at', '<=', now())
                ->count();

            $recentPeminjaman = Peminjaman::where('user_id', $user->id)
                ->with(['arsip.divisi', 'arsip.kategori'])
                ->latest()
                ->take(5)
                ->get();
        }

        // Additional data arrays for analytics: Upload Trend per Month
        $uploadTrend = Arsip::selectRaw("DATE_FORMAT(created_at, '%m-%Y') as month, count(*) as total")
            ->groupBy('month')
            ->orderBy('created_at', 'asc')
            ->get();

        $recentUploads = Arsip::with(['divisi', 'kategori'])
            ->latest()
            ->take(5)
            ->get();

        return [
            'totalArsip' => $totalArsip,
            'totalActivePeminjaman' => $totalActivePeminjaman,
            'totalUsers' => $totalUsers,
            'totalExpiredArsip' => $totalExpiredArsip,
            'totalPendingPeminjaman' => $totalPendingPeminjaman,
            'totalCompletedPeminjaman' => $totalCompletedPeminjaman,
            'divisiStats' => $divisiStats,
            'recentPeminjaman' => $recentPeminjaman,
            'myActivePeminjaman' => $myActivePeminjaman,
            'uploadTrend' => $uploadTrend,
            'recentUploads' => $recentUploads,
        ];
    }
}
