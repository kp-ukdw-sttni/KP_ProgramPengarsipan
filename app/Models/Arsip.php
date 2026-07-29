<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Arsip extends Model
{
    use HasFactory;

    protected $table = 'arsip';

    protected $fillable = [
        'nomor_arsip',
        'judul',
        'deskripsi',
        'kategori_id',
        'divisi_id',
        'file_path',
        'retention_date',
        'status',
    ];

    protected $casts = [
        'retention_date' => 'date',
    ];

    /**
     * Get the division that owns the archive.
     */
    public function divisi(): BelongsTo
    {
        return $this->belongsTo(Divisi::class, 'divisi_id');
    }

    /**
     * Get the category of the archive.
     */
    public function kategori(): BelongsTo
    {
        return $this->belongsTo(KategoriArsip::class, 'kategori_id');
    }

    /**
     * Get the borrow records for the archive.
     */
    public function peminjaman(): HasMany
    {
        return $this->hasMany(Peminjaman::class, 'arsip_id');
    }

    /**
     * Get the audit logs for this archive.
     */
    public function auditLogs(): HasMany
    {
        return $this->hasMany(AuditLog::class, 'arsip_id');
    }
}
