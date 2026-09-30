<?php

namespace App\Services;

use App\Models\Arsip;
use App\Models\Peminjaman;
use App\Models\User;
use App\Support\MasterData;

class DashboardService
{
    /**
     * Build the dashboard stats and recent data for internal campus users.
     */
    public function getDashboardData(User $user): array
    {
        $stats = MasterData::stats();
        $totalPendingPeminjaman = Peminjaman::where('status_approval', 'Pending')->count();

        $recentUploads = Arsip::with(['divisi', 'kategori', 'uploader'])
            ->latest()
            ->take(5)
            ->get();

        return [
            'totalArsip' => $stats['arsip'],
            'totalPendingPeminjaman' => $totalPendingPeminjaman,
            'totalKategori' => $stats['kategori'],
            'totalDivisi' => $stats['divisi'],
            'recentUploads' => $recentUploads,
        ];
    }
}
