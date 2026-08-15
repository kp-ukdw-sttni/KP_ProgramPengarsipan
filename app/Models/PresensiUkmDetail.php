<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class PresensiUkmDetail extends Model
{
    use HasFactory;

    protected $table = 'presensi_ukm_detail';

    protected $fillable = [
        'presensi_id',
        'anggota_id',
        'nama',
        'nim',
        'status_kehadiran',
        'keterangan',
    ];

    /**
     * Get the attendance session this row belongs to.
     */
    public function presensi(): BelongsTo
    {
        return $this->belongsTo(PresensiUkm::class, 'presensi_id');
    }

    /**
     * Get the UKM member this row refers to.
     */
    public function anggota(): BelongsTo
    {
        return $this->belongsTo(UkmAnggota::class, 'anggota_id');
    }
}
