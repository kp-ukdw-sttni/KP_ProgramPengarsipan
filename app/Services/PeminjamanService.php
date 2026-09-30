<?php

namespace App\Services;

use App\Models\Arsip;
use App\Models\AuditLog;
use App\Models\Peminjaman;
use App\Models\User;
use App\Support\AksesDokumen;
use Illuminate\Http\Request;

class PeminjamanService
{
    /**
     * Handle request access action from Karyawan.
     *
     * @return array{success: bool, message: string}
     */
    public function requestAccess(Arsip $arsip, User $user): array
    {
        if (in_array($arsip->status, ['Inaktif', 'Dimusnahkan'], true)) {
            return [
                'success' => false,
                'message' => 'Dokumen tidak dapat diakses karena status arsip ini '.$arsip->status.'.',
            ];
        }

        // Nothing to ask for when the document is already open to everyone.
        if (! AksesDokumen::perluPersetujuan($arsip)) {
            return [
                'success' => false,
                'message' => 'Dokumen ini tidak berstatus rahasia, sehingga tidak perlu persetujuan.',
            ];
        }

        if (AksesDokumen::canAccess($arsip, $user)) {
            return [
                'success' => false,
                'message' => 'Anda sudah memiliki akses ke dokumen ini.',
            ];
        }

        $existing = AksesDokumen::requestActive($arsip, $user);

        if ($existing && $existing->status_approval === 'Pending') {
            return [
                'success' => false,
                'message' => 'Permintaan akses untuk dokumen ini sedang menunggu persetujuan. Anda tidak dapat mengajukan ulang.',
            ];
        }

        // A previously decided request is reused rather than duplicated, so the
        // unique (arsip_id, user_id) index stays satisfied and the admin simply
        // sees one row per person.
        $peminjaman = AksesDokumen::requestTerakhir($arsip, $user) ?? new Peminjaman(['arsip_id' => $arsip->id, 'user_id' => $user->id]);
        $peminjaman->status_approval = 'Pending';
        $peminjaman->approved_by = null;
        $peminjaman->reviewed_at = null;
        $peminjaman->notes = null;
        $peminjaman->save();

        AuditLog::create([
            'user_id' => $user->id,
            'action' => 'Request Access',
            'arsip_id' => $arsip->id,
            'ip_address' => request()->ip(),
            'details' => "Mengajukan permintaan akses dokumen '{$arsip->judul}' (Nomor: {$arsip->nomor_arsip}).",
        ]);

        return [
            'success' => true,
            'message' => 'Permintaan akses berhasil diajukan dan menunggu persetujuan Admin.',
        ];
    }

    /**
     * Build the management listing for Admin, Operator and Staf TU.
     */
    public function getManageData(Request $request, User $user): array
    {
        $query = Peminjaman::with(['user', 'approver', 'arsip.divisi', 'arsip.kategori']);

        if ($request->filled('status')) {
            $query->where('status_approval', $request->status);
        }

        // Pending first, then by age. The previous FIELD() ordering was MySQL only,
        // and a portable CASE with bound placeholders mis-binds on SQLite, so the
        // status names are inlined from this class's own constant list.
        $peminjaman = $query
            ->orderByRaw("CASE status_approval WHEN 'Pending' THEN 0 WHEN 'Approved' THEN 1 WHEN 'Rejected' THEN 2 WHEN 'Expired' THEN 3 ELSE 4 END")
            ->orderByDesc('id')
            ->paginate(15)
            ->withQueryString();

        return [
            'peminjaman' => $peminjaman,
            'pendingCount' => Peminjaman::where('status_approval', 'Pending')->count(),
            'approvedCount' => Peminjaman::where('status_approval', 'Approved')->count(),
            'rejectedCount' => Peminjaman::where('status_approval', 'Rejected')->count(),
        ];
    }

    /**
     * Build the per-row decision hint shown to the approver, so a locked
     * document that the requester already has access to by other means is not
     * approved by accident.
     */
    public function getKonteks(Peminjaman $peminjaman): array
    {
        $arsip = $peminjaman->arsip;
        $pemohon = $peminjaman->user;

        return [
            'sudah_dimiliki_pengunggah' => $arsip && (int) $arsip->uploader_id === (int) $pemohon?->id,
            'tingkat_akses' => $arsip?->status_publikasi ?? '-',
        ];
    }

    /**
     * Approve a borrow request.
     */
    public function approve(array $data, Peminjaman $peminjaman, User $user): void
    {
        $peminjaman->update([
            'status_approval' => 'Approved',
            'borrowed_at' => now(),
            // Permanent grant: leaving expired_at null is what makes it permanent.
            'expired_at' => null,
            'reviewed_at' => now(),
            'approved_by' => $user->id,
            'notes' => $data['notes'] ?? null,
        ]);

        AuditLog::create([
            'user_id' => $user->id,
            'action' => 'Approve Access',
            'arsip_id' => $peminjaman->arsip_id,
            'ip_address' => request()->ip(),
            'details' => "Menyetujui permintaan akses dokumen '{$peminjaman->arsip->judul}' untuk '{$peminjaman->user->name}'. Izin akses bersifat permanen.",
        ]);
    }

    /**
     * Reject an access request with a mandatory reason.
     */
    public function reject(array $data, Peminjaman $peminjaman, User $user): void
    {
        $peminjaman->update([
            'status_approval' => 'Rejected',
            'reviewed_at' => now(),
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

    /**
     * Withdraw a previously granted permission.
     *
     * Keeping a Rejected row with a reason (rather than deleting it) means the
     * approval history stays auditable.
     */
    public function cabut(array $data, Peminjaman $peminjaman, User $user): void
    {
        abort_unless($peminjaman->status_approval === 'Approved', 422, 'Hanya izin yang sudah disetujui yang dapat dicabut.');

        $peminjaman->update([
            'status_approval' => 'Rejected',
            'reviewed_at' => now(),
            'approved_by' => $user->id,
            'notes' => $data['notes'],
        ]);

        AuditLog::create([
            'user_id' => $user->id,
            'action' => 'Revoke Access',
            'arsip_id' => $peminjaman->arsip_id,
            'ip_address' => request()->ip(),
            'details' => "Mencabut izin akses dokumen '{$peminjaman->arsip->judul}' dari '{$peminjaman->user->name}'. Alasan: {$data['notes']}",
        ]);
    }
}
