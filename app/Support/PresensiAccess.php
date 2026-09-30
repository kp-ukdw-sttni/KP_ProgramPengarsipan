<?php

namespace App\Support;

use App\Models\User;

/**
 * Single source of truth for who may fill in and review UKM attendance.
 *
 * Attendance is the responsibility of WK III Kemahasiswaan dan Alumni, so every
 * account sitting anywhere inside that unit qualifies — not just the ones holding
 * an explicit role. Roles still win, because Sie Kesiswaan can be posted
 * outside the unit and Admin/Superadmin supervise from the top.
 */
class PresensiAccess
{
    /**
     * The unit kerja kode that owns the attendance workflow.
     */
    public const PEMILIK_KODE = 'KMS';

    /**
     * Roles allowed to fill in attendance, regardless of where they are posted.
     *
     * @var list<string>
     */
    public const PENGISI_ROLES = ['Admin', 'Superadmin', 'Sie Kesiswaan'];

    /**
     * Roles allowed to verify or reject the archive generated from a session.
     *
     * @var list<string>
     */
    public const REVIEWER_ROLES = ['Admin', 'Superadmin'];

    /**
     * Can this user open the attendance form and submit a session?
     */
    public static function canAccess(User $user): bool
    {
        if ($user->hasAnyRole(self::PENGISI_ROLES)) {
            return true;
        }

        return self::isDalamBidangKemahasiswaan($user);
    }

    /**
     * Can this user verify or reject a submitted session?
     */
    public static function canReview(User $user): bool
    {
        return $user->hasAnyRole(self::REVIEWER_ROLES);
    }

    /**
     * Is the user posted inside WK III Kemahasiswaan dan Alumni?
     *
     * Walks up the parent chain instead of listing the child kode, so a sub-unit
     * added to the structure later keeps working without a code change.
     */
    public static function isDalamBidangKemahasiswaan(User $user): bool
    {
        $divisi = $user->divisi;

        while ($divisi) {
            if ($divisi->kode === self::PEMILIK_KODE) {
                return true;
            }

            $divisi = $divisi->parent;
        }

        return false;
    }
}
