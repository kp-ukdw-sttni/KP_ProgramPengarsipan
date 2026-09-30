<?php

namespace App\Models;

use App\Support\AksesDokumen;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Peminjaman extends Model
{
    use HasFactory;

    protected $table = 'peminjaman';

    protected $fillable = [
        'arsip_id',
        'user_id',
        'status_approval',
        'borrowed_at',
        'expired_at',
        'reviewed_at',
        'approved_by',
        'notes',
    ];

    protected $casts = [
        'borrowed_at' => 'datetime',
        'expired_at' => 'datetime',
        'reviewed_at' => 'datetime',
    ];

    /**
     * Get the archive being borrowed.
     */
    public function arsip(): BelongsTo
    {
        return $this->belongsTo(Arsip::class, 'arsip_id');
    }

    /**
     * Get the user who requested the borrow.
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    /**
     * Get the user who approved/rejected the request.
     */
    public function approver(): BelongsTo
    {
        return $this->belongsTo(User::class, 'approved_by');
    }

    /**
     * Decide whether this grant currently lets the requester read the document.
     *
     * Access is granted permanently: `Approved` is the only state that opens a
     * restricted document, and `expired_at` has been cleared for legacy
     * time-boxed grants by the migration. This mirrors
     * {@see AksesDokumen::hasAksesDisetujui()} on purpose — the
     * list flag and the download guard must never disagree.
     */
    public function isActive(): bool
    {
        return $this->status_approval === 'Approved';
    }

    /**
     * Has this request been decided?
     */
    public function sudahDiproses(): bool
    {
        return $this->status_approval !== 'Pending';
    }
}
