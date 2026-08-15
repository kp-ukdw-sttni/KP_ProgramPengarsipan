<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Ukm extends Model
{
    use HasFactory;

    protected $table = 'ukm';

    protected $fillable = [
        'kode',
        'name',
        'deskripsi',
        'pembina',
        'ketua',
        'divisi_id',
        'status',
    ];

    /**
     * Get the division supervising this UKM.
     */
    public function divisi(): BelongsTo
    {
        return $this->belongsTo(Divisi::class, 'divisi_id');
    }

    /**
     * Get the members of this UKM.
     */
    public function anggota(): HasMany
    {
        return $this->hasMany(UkmAnggota::class, 'ukm_id');
    }

    /**
     * Get the attendance sessions of this UKM.
     */
    public function presensi(): HasMany
    {
        return $this->hasMany(PresensiUkm::class, 'ukm_id');
    }
}
