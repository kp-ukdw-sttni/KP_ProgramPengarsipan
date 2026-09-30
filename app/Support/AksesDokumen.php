<?php

namespace App\Support;

use App\Models\Arsip;
use App\Models\Peminjaman;
use App\Models\User;

/**
 * Single source of truth for the "Persetujuan Akses" workflow.
 *
 * A Confidential archive stays locked to the uploader and the arsip managers.
 * Any other staff member who needs to read it asks for access, and an Admin or
 * Superadmin decides — once approved, that grant is permanent (the admin can
 * still revoke it by rejecting/clearing the row).
 */
class AksesDokumen
{
    /**
     * Publication levels every logged-in account may open freely.
     *
     * @var list<string>
     */
    public const TERBUKA = ['Public', 'Internal'];

    /**
     * Roles that manage archives and therefore never need to ask for access.
     *
     * @var list<string>
     */
    public const PENGELOLA_ROLES = ['Admin', 'Superadmin'];

    /**
     * Only these roles may approve or reject an access request.
     *
     * @var list<string>
     */
    public const PENYETUJU_ROLES = ['Admin', 'Superadmin'];

    /**
     * Does this document need a request before it can be opened?
     */
    public static function perluPersetujuan(Arsip $arsip): bool
    {
        return ! in_array($arsip->status_publikasi, self::TERBUKA, true);
    }

    /**
     * May this user open the file right now?
     */
    public static function canAccess(Arsip $arsip, User $user): bool
    {
        if ($user->hasAnyRole(self::PENGELOLA_ROLES)) {
            return true;
        }

        if ((int) $arsip->uploader_id === (int) $user->id) {
            return true;
        }

        if (! self::perluPersetujuan($arsip)) {
            return true;
        }

        return self::hasAksesDisetujui($arsip, $user);
    }

    /**
     * Has this user been granted permanent access to a restricted document?
     */
    public static function hasAksesDisetujui(Arsip $arsip, User $user): bool
    {
        return Peminjaman::where('arsip_id', $arsip->id)
            ->where('user_id', $user->id)
            ->where('status_approval', 'Approved')
            ->exists();
    }

    /**
     * May this user decide on access requests?
     */
    public static function canApprove(User $user): bool
    {
        return $user->hasAnyRole(self::PENYETUJU_ROLES);
    }

    /**
     * The user's most recent request for this document, whatever its status.
     *
     * Looked up without filtering on status so a resubmission after a rejection
     * reuses the existing row: the unique index on (arsip_id, user_id) means a
     * fresh insert would fail.
     */
    public static function requestTerakhir(Arsip $arsip, User $user): ?Peminjaman
    {
        return Peminjaman::where('arsip_id', $arsip->id)
            ->where('user_id', $user->id)
            ->latest('id')
            ->first();
    }

    /**
     * A request that still blocks a new one, i.e. awaiting a decision or
     * already granted.
     */
    public static function requestActive(Arsip $arsip, User $user): ?Peminjaman
    {
        return Peminjaman::where('arsip_id', $arsip->id)
            ->where('user_id', $user->id)
            ->whereIn('status_approval', ['Pending', 'Approved'])
            ->latest('id')
            ->first();
    }
}
