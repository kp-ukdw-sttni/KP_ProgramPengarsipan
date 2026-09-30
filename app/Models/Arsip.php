<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;
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
        'study_program_id',
        'nama_prodi',
        'tahun',
        'tanggal_dokumen',
        'tanggal_diterima',
        'pengirim',
        'penerima',
        'file_path',
        'file_path_excel',
        'lokasi_fisik',
        'file_size',
        'file_mime',
        'retention_date',
        'status',
        'verification_status',
        'tags',
        'status_publikasi',
        'uploader_id',
        'presensi_ukm_id',
    ];

    protected $casts = [
        'retention_date' => 'date',
        'tanggal_dokumen' => 'date',
        'tanggal_diterima' => 'date',
        'tahun' => 'integer',
        'file_size' => 'integer',
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
            $details = $arsip->isForceDeleting()
                ? "Menghapus permanen arsip '{$arsip->judul}' (Nomor: {$arsip->nomor_arsip}, ID: {$arsip->id})."
                : "Menghapus (Soft Delete) arsip '{$arsip->judul}' (Nomor: {$arsip->nomor_arsip}).";

            self::logAction('Delete', $arsip, $details);
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
            // A force deleted record is already gone, so the foreign key has to
            // stay null or the insert fails on audit_logs_arsip_id_foreign.
            'arsip_id' => $arsip->isForceDeleting() ? null : $arsip->id,
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
     * Get the study program the archive belongs to.
     */
    public function studyProgram(): BelongsTo
    {
        return $this->belongsTo(StudyProgram::class, 'study_program_id');
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

    /**
     * Get the attendance session that generated this archive.
     */
    public function presensiUkm(): BelongsTo
    {
        return $this->belongsTo(PresensiUkm::class, 'presensi_ukm_id');
    }
}
