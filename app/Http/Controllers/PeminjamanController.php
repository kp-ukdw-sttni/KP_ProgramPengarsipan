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
        
        // Karyawan can only see their own borrowing requests
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

        // Check if archive is Expired
        if ($arsip->status === 'Expired') {
            return back()->with('error', 'Dokumen tidak dapat dipinjam karena masa retensi telah habis.');
        }

        // Check if there is already an active or pending request for this user
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
            // If approved but expired, we can create a new request
        }

        Peminjaman::create([
            'arsip_id' => $arsip->id,
            'user_id' => $user->id,
            'status_approval' => 'Pending',
        ]);

        // Log this request in AuditLog
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
     * Display listing of pending requests for Admin and Operator.
     */
    public function manage()
    {
        $user = Auth::user();

        $query = Peminjaman::with(['user', 'arsip.divisi', 'arsip.kategori']);

        if ($user->hasRole('Operator')) {
            // Operator only sees requests for their division
            $divisiId = $user->divisi_id;
            $query->whereHas('arsip', function ($q) use ($divisiId) {
                $q->where('divisi_id', $divisiId);
            });
        }

        // Sort by Pending first, then latest
        $peminjaman = $query->orderByRaw("FIELD(status_approval, 'Pending', 'Approved', 'Rejected', 'Expired') ASC")
            ->latest()
            ->paginate(15);

        return view('peminjaman.manage', compact('peminjaman'));
    }

    /**
     * Approve a borrow request.
     */
    public function approve(Request $request, Peminjaman $peminjaman)
    {
        $user = Auth::user();

        // Authorization check: Operator can only approve for their division
        if ($user->hasRole('Operator') && $peminjaman->arsip->divisi_id != $user->divisi_id) {
            abort(403, 'Anda tidak diizinkan menyetujui permintaan dokumen dari divisi lain.');
        }

        $request->validate([
            'duration' => ['required', 'integer', 'min:1', 'max:168'], // min 1 hour, max 1 week (168 hours)
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

        // Log this approval in AuditLog
        AuditLog::create([
            'user_id' => $user->id,
            'action' => 'Approve Access',
            'arsip_id' => $peminjaman->arsip_id,
            'ip_address' => request()->ip(),
            'details' => "Menyetujui permintaan akses dokumen '{$peminjaman->arsip->judul}' untuk Karyawan '{$peminjaman->user->name}' selama {$request->duration} jam.",
        ]);

        return redirect()->route('peminjaman.manage')->with('success', 'Permintaan peminjaman berhasil disetujui.');
    }

    /**
     * Reject a borrow request.
     */
    public function reject(Request $request, Peminjaman $peminjaman)
    {
        $user = Auth::user();

        // Authorization check
        if ($user->hasRole('Operator') && $peminjaman->arsip->divisi_id != $user->divisi_id) {
            abort(403, 'Anda tidak diizinkan menolak permintaan dokumen dari divisi lain.');
        }

        $peminjaman->update([
            'status_approval' => 'Rejected',
            'approved_by' => $user->id,
            'notes' => $request->notes ?? 'Permintaan akses ditolak oleh Operator.',
        ]);

        // Log this rejection in AuditLog
        AuditLog::create([
            'user_id' => $user->id,
            'action' => 'Reject Access',
            'arsip_id' => $peminjaman->arsip_id,
            'ip_address' => request()->ip(),
            'details' => "Menolak permintaan akses dokumen '{$peminjaman->arsip->judul}' untuk Karyawan '{$peminjaman->user->name}'. Alasan: " . ($request->notes ?? 'Tidak ditentukan'),
        ]);

        return redirect()->route('peminjaman.manage')->with('success', 'Permintaan peminjaman telah ditolak.');
    }
}
