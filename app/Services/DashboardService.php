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
        $totalDownloads = 0; // AuditLog "Download" actions
        $totalUsers = 0;
        $totalAktifArsip = 0;
        $totalInaktifArsip = 0;
        $totalDiarsipkanArsip = 0;
        $totalDimusnahkanArsip = 0;
        $totalPendingPeminjaman = 0;
        $totalCompletedPeminjaman = 0;
        $divisiStats = collect();
        $recentPeminjaman = collect();
        $myActivePeminjaman = collect();

        if ($user->hasRole('Superadmin')) {
            $totalArsip = Arsip::count();
            $totalDownloads = AuditLog::where('action', 'Download')->count(); // Total Unduhan
            $totalUsers = User::count();
            $totalAktifArsip = Arsip::where('status', 'Aktif')->count();
            $totalInaktifArsip = Arsip::where('status', 'Inaktif')->count();
            $totalDiarsipkanArsip = Arsip::where('status', 'Diarsipkan')->count();
            $totalDimusnahkanArsip = Arsip::where('status', 'Dimusnahkan')->count();
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
            $totalDownloads = AuditLog::where('action', 'Download')
                ->whereHas('arsip', function ($query) use ($divisiId) {
                    $query->where('divisi_id', $divisiId);
                })
                ->count();
            $totalUsers = User::where('divisi_id', $divisiId)->count();
            $totalAktifArsip = Arsip::where('divisi_id', $divisiId)->where('status', 'Aktif')->count();
            $totalInaktifArsip = Arsip::where('divisi_id', $divisiId)->where('status', 'Inaktif')->count();
            $totalDiarsipkanArsip = Arsip::where('divisi_id', $divisiId)->where('status', 'Diarsipkan')->count();
            $totalDimusnahkanArsip = Arsip::where('divisi_id', $divisiId)->where('status', 'Dimusnahkan')->count();
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
            $totalAktifArsip = Arsip::where('status', 'Aktif')->count();
            $totalArsip = $totalAktifArsip;
            $totalInaktifArsip = Arsip::where('status', 'Inaktif')->count();
            $totalDiarsipkanArsip = Arsip::where('status', 'Diarsipkan')->count();
            $totalDimusnahkanArsip = Arsip::where('status', 'Dimusnahkan')->count();

            $myActivePeminjaman = Peminjaman::where('user_id', $user->id)
                ->where('status_approval', 'Approved')
                ->where('expired_at', '>', now())
                ->with('arsip')
                ->get();

            $totalDownloads = AuditLog::where('user_id', $user->id)->where('action', 'Download')->count(); // Personal Downloads
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
            'totalDownloads' => $totalDownloads,
            'totalUsers' => $totalUsers,
            'totalAktifArsip' => $totalAktifArsip,
            'totalInaktifArsip' => $totalInaktifArsip,
            'totalDiarsipkanArsip' => $totalDiarsipkanArsip,
            'totalDimusnahkanArsip' => $totalDimusnahkanArsip,
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
