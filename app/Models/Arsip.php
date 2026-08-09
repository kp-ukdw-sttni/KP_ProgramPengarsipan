<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Facades\Auth;

class Arsip extends Model
{
    use HasFactory, SoftDeletes;

    protected $table = 'arsip';

    protected $fillable = [
        'nomor_arsip',
        'nomor_surat',
        'judul',
        'deskripsi',
        'kategori_id',
        'divisi_id',
        'file_path',
        'retention_date',
        'status',
        'tags',
        'status_publikasi',
        'uploader_id',
    ];

    protected $casts = [
        'retention_date' => 'date',
    ];

    protected static function booted()
    {
        static::created(function ($arsip) {
            self::logAction('Create', $arsip, "Membuat arsip baru '{$arsip->judul}' (Nomor: {$arsip->nomor_arsip}).");
        });

        static::updated(function ($arsip) {
            self::logAction('Update', $arsip, "Memperbarui arsip '{$arsip->judul}' (Nomor: {$arsip->nomor_arsip}).");
        });

        static::deleted(function ($arsip) {
            self::logAction('Delete', $arsip, "Menghapus (Soft Delete) arsip '{$arsip->judul}' (Nomor: {$arsip->nomor_arsip}).");
        });

        static::restored(function ($arsip) {
            self::logAction('Restore', $arsip, "Memulihkan arsip '{$arsip->judul}' (Nomor: {$arsip->nomor_arsip}) dari Recycle Bin.");
        });
    }

    private static function logAction($action, $arsip, $details)
    {
        AuditLog::create([
            'user_id' => Auth::id() ?? $arsip->uploader_id,
            'action' => $action,
            'arsip_id' => $arsip->id,
            'ip_address' => request()->ip(),
            'details' => $details,
        ]);
    }

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

    /**
     * Get the user who uploaded the archive.
     */
    public function uploader(): BelongsTo
    {
        return $this->belongsTo(User::class, 'uploader_id');
    }

    /**
     * Get the historical versions of the document.
     */
    public function versions(): HasMany
    {
        return $this->hasMany(ArsipVersion::class, 'arsip_id');
    }
}
