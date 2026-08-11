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
     * Display a listing of user's own borrowing requests.
     */
    public function index()
    {
        $user = Auth::user();

        return Inertia::render('Peminjaman/Index', [
            'peminjaman' => $this->peminjamanService->getIndexData($user),
        ]);
    }

    /**
     * Handle request access action from Karyawan.
     */
    public function requestAccess(Arsip $arsip)
    {
        $result = $this->peminjamanService->requestAccess($arsip, Auth::user());

        if ($result['target'] === 'back') {
            return back()->with($result['success'] ? 'success' : 'error', $result['message']);
        }

        return redirect()->route('peminjaman.index')
            ->with($result['success'] ? 'success' : 'error', $result['message']);
    }

    /**
     * Cancel a pending borrow request (only if status is Pending).
     */
    public function cancelRequest(Peminjaman $peminjaman)
    {
        $result = $this->peminjamanService->cancelRequest($peminjaman, Auth::user());

        return redirect()->route('peminjaman.index')
            ->with($result['success'] ? 'success' : 'error', $result['message']);
    }

    /**
     * Display listing of pending requests for Admin and Operator.
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
        $user = Auth::user();

        if (($user->hasRole('Operator') || $user->hasRole('Staf TU')) && $peminjaman->arsip->divisi_id != $user->divisi_id) {
            abort(403, 'Anda tidak diizinkan menyetujui permintaan dokumen dari divisi lain.');
        }

        $request->validate([
            'duration' => ['required', 'integer', 'min:1', 'max:168'],
        ]);

        $this->peminjamanService->approve($request->only(['duration', 'notes']), $peminjaman, $user);

        return redirect()->route('peminjaman.manage')->with('success', 'Permintaan peminjaman berhasil disetujui.');
    }

    /**
     * Reject a borrow request with a mandatory rejection note.
     */
    public function reject(Request $request, Peminjaman $peminjaman)
    {
        $user = Auth::user();

        if (($user->hasRole('Operator') || $user->hasRole('Staf TU')) && $peminjaman->arsip->divisi_id != $user->divisi_id) {
            abort(403, 'Anda tidak diizinkan menolak permintaan dokumen dari divisi lain.');
        }

        $request->validate([
            'notes' => ['required', 'string', 'max:500'],
        ]);

        $this->peminjamanService->reject($request->only(['notes']), $peminjaman, $user);

        return redirect()->route('peminjaman.manage')->with('success', 'Permintaan peminjaman telah ditolak.');
    }
}
