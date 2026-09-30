<?php

namespace App\Http\Controllers;

use App\Models\Arsip;
use App\Models\Peminjaman;
use App\Services\PeminjamanService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Inertia\Inertia;

class PeminjamanController extends Controller
{
    public function __construct(private PeminjamanService $peminjamanService) {}

    /**
     * Handle request access action from Karyawan.
     *
     * The requester has no page of their own: the outcome is shown inline on
     * the locked document in the arsip list, and the Admin decides from the
     * Persetujuan Akses queue.
     */
    public function requestAccess(Arsip $arsip)
    {
        $result = $this->peminjamanService->requestAccess($arsip, Auth::user());

        return back()->with($result['success'] ? 'success' : 'error', $result['message']);
    }

    /**
     * Withdraw a permission that was granted earlier.
     */
    public function revoke(Request $request, Peminjaman $peminjaman)
    {
        $request->validate([
            'notes' => ['required', 'string', 'max:500'],
        ]);

        $this->peminjamanService->cabut($request->only('notes'), $peminjaman, Auth::user());

        return back()->with('success', 'Izin akses telah dicabut.');
    }

    /**
     * Display listing of pending requests for Admin and Superadmin.
     */
    public function manage(Request $request)
    {
        $user = Auth::user();

        return Inertia::render('Peminjaman/Manage', $this->peminjamanService->getManageData($request, $user));
    }

    /**
     * Approve a borrow request.
     */
    public function approve(Request $request, Peminjaman $peminjaman)
    {
        abort_unless($peminjaman->status_approval === 'Pending', 422, 'Permintaan ini sudah diproses.');

        $request->validate([
            'notes' => ['nullable', 'string', 'max:500'],
        ]);

        $this->peminjamanService->approve($request->only('notes'), $peminjaman, Auth::user());

        return back()->with('success', 'Permintaan akses disetujui. Pemohon sekarang dapat membuka dokumen ini.');
    }

    /**
     * Reject a borrow request with a mandatory rejection note.
     */
    public function reject(Request $request, Peminjaman $peminjaman)
    {
        abort_unless($peminjaman->status_approval === 'Pending', 422, 'Permintaan ini sudah diproses.');

        $request->validate([
            'notes' => ['required', 'string', 'max:500'],
        ]);

        $this->peminjamanService->reject($request->only('notes'), $peminjaman, Auth::user());

        return back()->with('success', 'Permintaan akses ditolak.');
    }
}
