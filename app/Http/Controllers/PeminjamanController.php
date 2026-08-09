<?php

namespace App\Http\Controllers;

use App\Models\Peminjaman;
use App\Models\Arsip;
use App\Models\AuditLog;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class PeminjamanController extends Controller
{
    /**
     * Display a listing of user's own borrowing requests.
     */
    public function index()
    {
        $user = Auth::user();
        
        $peminjaman = Peminjaman::where('user_id', $user->id)
            ->with(['arsip.divisi', 'arsip.kategori', 'approver'])
            ->latest()
            ->paginate(10);

        return view('peminjaman.index', compact('peminjaman'));
    }

    /**
     * Handle request access action from Karyawan.
     */
    public function requestAccess(Arsip $arsip)
    {
        $user = Auth::user();

        if ($arsip->status === 'Expired') {
            return back()->with('error', 'Dokumen tidak dapat dipinjam karena masa retensi telah habis.');
        }

        $existing = Peminjaman::where('arsip_id', $arsip->id)
            ->where('user_id', $user->id)
            ->whereIn('status_approval', ['Pending', 'Approved'])
            ->first();

        if ($existing) {
            if ($existing->status_approval === 'Pending') {
                return back()->with('error', 'Permintaan akses untuk dokumen ini sedang menunggu persetujuan.');
            }
            if ($existing->isActive()) {
                return back()->with('success', 'Akses Anda ke dokumen ini masih aktif hingga ' . $existing->expired_at->format('d M Y H:i'));
            }
        }

        Peminjaman::create([
            'arsip_id' => $arsip->id,
            'user_id' => $user->id,
            'status_approval' => 'Pending',
        ]);

        AuditLog::create([
            'user_id' => $user->id,
            'action' => 'Request Access',
            'arsip_id' => $arsip->id,
            'ip_address' => request()->ip(),
            'details' => "Mengajukan permintaan peminjaman dokumen '{$arsip->judul}' (Nomor: {$arsip->nomor_arsip}).",
        ]);

        return redirect()->route('peminjaman.index')->with('success', 'Permintaan akses berhasil diajukan. Menunggu persetujuan Operator.');
    }

    /**
     * Cancel a pending borrow request (only if status is Pending).
     */
    public function cancelRequest(Peminjaman $peminjaman)
    {
        $user = Auth::user();

        // Only the owner of the request can cancel it
        if ($peminjaman->user_id !== $user->id) {
            abort(403, 'Anda tidak diizinkan membatalkan permintaan orang lain.');
        }

        // Can only cancel if status is still Pending
        if ($peminjaman->status_approval !== 'Pending') {
            return redirect()->route('peminjaman.index')
                ->with('error', 'Permintaan yang sudah diproses tidak dapat dibatalkan.');
        }

        AuditLog::create([
            'user_id'    => $user->id,
            'action'     => 'Cancel Request',
            'arsip_id'   => $peminjaman->arsip_id,
            'ip_address' => request()->ip(),
            'details'    => "Membatalkan permintaan akses dokumen ID #{$peminjaman->arsip_id}.",
        ]);

        $peminjaman->delete();

        return redirect()->route('peminjaman.index')->with('success', 'Permintaan akses berhasil dibatalkan.');
    }

    /**
     * Display listing of pending requests for Admin and Operator.
     */
    public function manage(Request $request)
    {
        $user = Auth::user();

        $query = Peminjaman::with(['user', 'arsip.divisi', 'arsip.kategori']);

        if ($user->hasRole('Operator')) {
            $divisiId = $user->divisi_id;
            $query->whereHas('arsip', function ($q) use ($divisiId) {
                $q->where('divisi_id', $divisiId);
            });
        }

        // Filter by status tab
        if ($request->filled('status')) {
            $query->where('status_approval', $request->status);
        }

        $peminjaman = $query->orderByRaw("FIELD(status_approval, 'Pending', 'Approved', 'Rejected', 'Expired') ASC")
            ->latest()
            ->paginate(15);

        $pendingCount   = Peminjaman::when($user->hasRole('Operator'), fn ($q) => $q->whereHas('arsip', fn ($q2) => $q2->where('divisi_id', $user->divisi_id)))->where('status_approval', 'Pending')->count();
        $approvedCount  = Peminjaman::when($user->hasRole('Operator'), fn ($q) => $q->whereHas('arsip', fn ($q2) => $q2->where('divisi_id', $user->divisi_id)))->where('status_approval', 'Approved')->count();
        $rejectedCount  = Peminjaman::when($user->hasRole('Operator'), fn ($q) => $q->whereHas('arsip', fn ($q2) => $q2->where('divisi_id', $user->divisi_id)))->where('status_approval', 'Rejected')->count();

        return view('peminjaman.manage', compact('peminjaman', 'pendingCount', 'approvedCount', 'rejectedCount'));
    }

    /**
     * Approve a borrow request.
     */
    public function approve(Request $request, Peminjaman $peminjaman)
    {
        $user = Auth::user();

        if ($user->hasRole('Operator') && $peminjaman->arsip->divisi_id != $user->divisi_id) {
            abort(403, 'Anda tidak diizinkan menyetujui permintaan dokumen dari divisi lain.');
        }

        $request->validate([
            'duration' => ['required', 'integer', 'min:1', 'max:168'],
        ]);

        $borrowedAt = now();
        $expiredAt = now()->addHours($request->duration);

        $peminjaman->update([
            'status_approval' => 'Approved',
            'borrowed_at' => $borrowedAt,
            'expired_at' => $expiredAt,
            'approved_by' => $user->id,
            'notes' => $request->notes,
        ]);

        AuditLog::create([
            'user_id'    => $user->id,
            'action'     => 'Approve Access',
            'arsip_id'   => $peminjaman->arsip_id,
            'ip_address' => request()->ip(),
            'details'    => "Menyetujui permintaan akses dokumen '{$peminjaman->arsip->judul}' untuk '{$peminjaman->user->name}' selama {$request->duration} jam.",
        ]);

        return redirect()->route('peminjaman.manage')->with('success', 'Permintaan peminjaman berhasil disetujui.');
    }

    /**
     * Reject a borrow request with a mandatory rejection note.
     */
    public function reject(Request $request, Peminjaman $peminjaman)
    {
        $user = Auth::user();

        if ($user->hasRole('Operator') && $peminjaman->arsip->divisi_id != $user->divisi_id) {
            abort(403, 'Anda tidak diizinkan menolak permintaan dokumen dari divisi lain.');
        }

        $request->validate([
            'notes' => ['required', 'string', 'max:500'],
        ]);

        $peminjaman->update([
            'status_approval' => 'Rejected',
            'approved_by'     => $user->id,
            'notes'           => $request->notes,
        ]);

        AuditLog::create([
            'user_id'    => $user->id,
            'action'     => 'Reject Access',
            'arsip_id'   => $peminjaman->arsip_id,
            'ip_address' => request()->ip(),
            'details'    => "Menolak permintaan akses dokumen '{$peminjaman->arsip->judul}' untuk '{$peminjaman->user->name}'. Alasan: {$request->notes}",
        ]);

        return redirect()->route('peminjaman.manage')->with('success', 'Permintaan peminjaman telah ditolak.');
    }
}
