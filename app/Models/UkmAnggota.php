<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class UkmAnggota extends Model
{
    use HasFactory;

    protected $table = 'ukm_anggota';

    protected $fillable = [
        'ukm_id',
        'nama',
        'nim',
        'jabatan',
        'status_keanggotaan',
    ];

    /**
     * Get the UKM that owns this member.
     */
    public function ukm(): BelongsTo
    {
        return $this->belongsTo(Ukm::class, 'ukm_id');
    }

    /**
     * Get the attendance detail records for this member.
     */
    public function detailPresensi(): HasMany
    {
        return $this->hasMany(PresensiUkmDetail::class, 'anggota_id');
    }
}
