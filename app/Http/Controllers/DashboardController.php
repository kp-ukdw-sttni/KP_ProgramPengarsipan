<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Models\Divisi;
use App\Models\Arsip;
use App\Models\Peminjaman;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class DashboardController extends Controller
{
    /**
     * Display the dashboard.
     */
    public function index()
    {
        $user = Auth::user();
        
        // Define base variables
        $totalArsip = 0;
        $totalActivePeminjaman = 0;
        $totalUsers = 0;
        $totalExpiredArsip = 0;
        $totalPendingPeminjaman = 0;
        $totalCompletedPeminjaman = 0;
        $divisiStats = collect();
        $recentPeminjaman = collect();
        $myActivePeminjaman = collect();

        if ($user->hasRole('Superadmin')) {
            // Superadmin sees global stats
            $totalArsip = Arsip::count();
            $totalActivePeminjaman = Peminjaman::where('status_approval', 'Approved')
                ->where('expired_at', '>', now())
                ->count();
            $totalUsers = User::count();
            $totalExpiredArsip = Arsip::where('status', 'Expired')->count();
            $totalPendingPeminjaman = Peminjaman::where('status_approval', 'Pending')->count();
            $totalCompletedPeminjaman = Peminjaman::where('status_approval', 'Approved')
                ->where('expired_at', '<=', now())
                ->count();
            
            // Stats per division
            $divisiStats = Divisi::withCount('arsip')->get();

            // Recent borrow requests
            $recentPeminjaman = Peminjaman::with(['user', 'arsip'])
                ->latest()
                ->take(5)
                ->get();

        } elseif ($user->hasRole('Operator')) {
            // Operator sees division-specific stats
            $divisiId = $user->divisi_id;
            
            $totalArsip = Arsip::where('divisi_id', $divisiId)->count();
            $totalActivePeminjaman = Peminjaman::whereHas('arsip', function ($query) use ($divisiId) {
                    $query->where('divisi_id', $divisiId);
                })
                ->where('status_approval', 'Approved')
                ->where('expired_at', '>', now())
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

            // Recent borrow requests for their division
            $recentPeminjaman = Peminjaman::whereHas('arsip', function ($query) use ($divisiId) {
                    $query->where('divisi_id', $divisiId);
                })
                ->with(['user', 'arsip'])
                ->latest()
                ->take(5)
                ->get();
                
            $divisiStats = Divisi::where('id', $divisiId)->withCount('arsip')->get();

        } else {
            // Karyawan sees their own statistics and division name
            $totalArsip = Arsip::where('status', 'Aktif')->count();
            
            $myActivePeminjaman = Peminjaman::where('user_id', $user->id)
                ->where('status_approval', 'Approved')
                ->where('expired_at', '>', now())
                ->with('arsip')
                ->get();
                
            $totalActivePeminjaman = $myActivePeminjaman->count();
            $totalExpiredArsip = Arsip::where('status', 'Expired')->count();
            $totalPendingPeminjaman = Peminjaman::where('user_id', $user->id)
                ->where('status_approval', 'Pending')
                ->count();
            $totalCompletedPeminjaman = Peminjaman::where('user_id', $user->id)
                ->where('status_approval', 'Approved')
                ->where('expired_at', '<=', now())
                ->count();
            
            $recentPeminjaman = Peminjaman::where('user_id', $user->id)
                ->with('arsip')
                ->latest()
                ->take(5)
                ->get();
        }

        return view('dashboard', compact(
            'totalArsip',
            'totalActivePeminjaman',
            'totalUsers',
            'totalExpiredArsip',
            'totalPendingPeminjaman',
            'totalCompletedPeminjaman',
            'divisiStats',
            'recentPeminjaman',
            'myActivePeminjaman'
        ));
    }
}
