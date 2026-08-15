<?php

namespace App\Services;

use App\Models\Arsip;
use App\Models\AuditLog;
use App\Models\Peminjaman;
use App\Models\User;
use Illuminate\Http\Request;

class PeminjamanService
{
    /**
     * Build the listing of the user's own borrowing requests.
     */
    public function getIndexData(User $user)
    {
        return Peminjaman::where('user_id', $user->id)
            ->with(['arsip.divisi', 'arsip.kategori', 'approver'])
            ->latest()
            ->paginate(10);
    }

    /**
     * Handle request access action from Karyawan.
     *
     * @return array{target: string, success: bool, message: string}
     */
    public function requestAccess(Arsip $arsip, User $user): array
    {
        if (in_array($arsip->status, ['Inaktif', 'Dimusnahkan'])) {
            return [
                'target' => 'back',
                'success' => false,
                'message' => 'Dokumen tidak dapat dipinjam karena status arsip ini '.$arsip->status.'.',
            ];
        }

        $existing = Peminjaman::where('arsip_id', $arsip->id)
            ->where('user_id', $user->id)
            ->whereIn('status_approval', ['Pending', 'Approved'])
            ->first();

        if ($existing) {
            if ($existing->status_approval === 'Pending') {
                return [
                    'target' => 'back',
                    'success' => false,
                    'message' => 'Permintaan akses untuk dokumen ini sedang menunggu persetujuan.',
                ];
            }
            if ($existing->isActive()) {
                return [
                    'target' => 'back',
                    'success' => true,
                    'message' => 'Akses Anda ke dokumen ini masih aktif hingga '.$existing->expired_at->format('d M Y H:i'),
                ];
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

        return [
            'target' => 'peminjaman.index',
            'success' => true,
            'message' => 'Permintaan akses berhasil diajukan. Menunggu persetujuan Operator.',
        ];
    }

    /**
     * Cancel a pending borrow request (only if status is Pending).
     *
     * @return array{target: string, success: bool, message: string}
     */
    public function cancelRequest(Peminjaman $peminjaman, User $user): array
    {
        // Only the owner of the request can cancel it
        if ($peminjaman->user_id !== $user->id) {
            abort(403, 'Anda tidak diizinkan membatalkan permintaan orang lain.');
        }

        // Can only cancel if status is still Pending
        if ($peminjaman->status_approval !== 'Pending') {
            return [
                'target' => 'peminjaman.index',
                'success' => false,
                'message' => 'Permintaan yang sudah diproses tidak dapat dibatalkan.',
            ];
        }

        AuditLog::create([
            'user_id' => $user->id,
            'action' => 'Cancel Request',
            'arsip_id' => $peminjaman->arsip_id,
            'ip_address' => request()->ip(),
            'details' => "Membatalkan permintaan akses dokumen ID #{$peminjaman->arsip_id}.",
        ]);

        $peminjaman->delete();

        return [
            'target' => 'peminjaman.index',
            'success' => true,
            'message' => 'Permintaan akses berhasil dibatalkan.',
        ];
    }

    /**
     * Build the management listing for Admin, Operator and Staf TU.
     */
    public function getManageData(Request $request, User $user): array
    {
        $query = Peminjaman::with(['user', 'arsip.divisi', 'arsip.kategori']);

        if ($user->hasRole('Operator') || $user->hasRole('Staf TU')) {
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
            ->paginate(15)
            ->withQueryString();

        $staffRole = fn ($q) => $q->whereHas('arsip', fn ($q2) => $q2->where('divisi_id', $user->divisi_id));
        $pendingCount = Peminjaman::when($user->hasRole('Operator') || $user->hasRole('Staf TU'), $staffRole)->where('status_approval', 'Pending')->count();
        $approvedCount = Peminjaman::when($user->hasRole('Operator') || $user->hasRole('Staf TU'), $staffRole)->where('status_approval', 'Approved')->count();
        $rejectedCount = Peminjaman::when($user->hasRole('Operator') || $user->hasRole('Staf TU'), $staffRole)->where('status_approval', 'Rejected')->count();

        return [
            'peminjaman' => $peminjaman,
            'pendingCount' => $pendingCount,
            'approvedCount' => $approvedCount,
            'rejectedCount' => $rejectedCount,
        ];
    }

    /**
     * Approve a borrow request.
     */
    public function approve(array $data, Peminjaman $peminjaman, User $user): void
    {
        $borrowedAt = now();
        $expiredAt = now()->addHours($data['duration']);

        $peminjaman->update([
            'status_approval' => 'Approved',
            'borrowed_at' => $borrowedAt,
            'expired_at' => $expiredAt,
            'approved_by' => $user->id,
            'notes' => $data['notes'] ?? null,
        ]);

        AuditLog::create([
            'user_id' => $user->id,
            'action' => 'Approve Access',
            'arsip_id' => $peminjaman->arsip_id,
            'ip_address' => request()->ip(),
            'details' => "Menyetujui permintaan akses dokumen '{$peminjaman->arsip->judul}' untuk '{$peminjaman->user->name}' selama {$data['duration']} jam.",
        ]);
    }

    /**
     * Reject a borrow request with a mandatory rejection note.
     */
    public function reject(array $data, Peminjaman $peminjaman, User $user): void
    {
        $peminjaman->update([
            'status_approval' => 'Rejected',
            'approved_by' => $user->id,
            'notes' => $data['notes'],
        ]);

        AuditLog::create([
            'user_id' => $user->id,
            'action' => 'Reject Access',
            'arsip_id' => $peminjaman->arsip_id,
            'ip_address' => request()->ip(),
            'details' => "Menolak permintaan akses dokumen '{$peminjaman->arsip->judul}' untuk '{$peminjaman->user->name}'. Alasan: {$data['notes']}",
        ]);
    }
}
