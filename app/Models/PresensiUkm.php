<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class PresensiUkm extends Model
{
    use HasFactory;

    protected $table = 'presensi_ukm';

    protected $fillable = [
        'ukm_id',
        'judul_kegiatan',
        'tanggal_kegiatan',
        'pertemuan_ke',
        'pengisi_id',
        'status_arsip',
        'arsip_id',
        'catatan_pengisi',
        'catatan_reviewer',
        'reviewer_id',
        'reviewed_at',
    ];

    protected $casts = [
        'tanggal_kegiatan' => 'date',
        'pertemuan_ke' => 'integer',
        'reviewed_at' => 'datetime',
    ];

    /**
     * Get the UKM this attendance belongs to.
     */
    public function ukm(): BelongsTo
    {
        return $this->belongsTo(Ukm::class, 'ukm_id');
    }

    /**
     * Get the user who filled the attendance.
     */
    public function pengisi(): BelongsTo
    {
        return $this->belongsTo(User::class, 'pengisi_id');
    }

    /**
     * Get the reviewer (Superadmin) who verified the archive.
     */
    public function reviewer(): BelongsTo
    {
        return $this->belongsTo(User::class, 'reviewer_id');
    }

    /**
     * Get the archive generated from this attendance.
     */
    public function arsip(): BelongsTo
    {
        return $this->belongsTo(Arsip::class, 'arsip_id');
    }

    /**
     * Get the attendance detail rows.
     */
    public function details(): HasMany
    {
        return $this->hasMany(PresensiUkmDetail::class, 'presensi_id');
    }

    /**
     * Count of attendance by status for this session.
     */
    public function hitungKehadiran(string $status): int
    {
        return $this->details->where('status_kehadiran', $status)->count();
    }
}
