<?php

namespace App\Services;

use App\Models\Arsip;
use App\Models\Divisi;
use App\Models\KategoriArsip;
use App\Models\Peminjaman;
use App\Models\User;

class DashboardService
{
    /**
     * Build the dashboard stats and recent data for internal campus users.
     */
    public function getDashboardData(User $user): array
    {
        $totalArsip = Arsip::count();
        $totalPendingPeminjaman = Peminjaman::where('status_approval', 'Pending')->count();
        $totalKategori = KategoriArsip::whereNull('parent_id')->count();
        $totalDivisi = Divisi::count();

        $recentUploads = Arsip::with(['divisi', 'kategori', 'uploader'])
            ->latest()
            ->take(5)
            ->get();

        return [
            'totalArsip' => $totalArsip,
            'totalPendingPeminjaman' => $totalPendingPeminjaman,
            'totalKategori' => $totalKategori,
            'totalDivisi' => $totalDivisi,
            'recentUploads' => $recentUploads,
        ];
    }
}
