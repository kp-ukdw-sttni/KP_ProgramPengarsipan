<?php

namespace App\Models;

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
        'approved_by',
        'notes',
    ];

    protected $casts = [
        'borrowed_at' => 'datetime',
        'expired_at' => 'datetime',
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
     * Determine if this borrowing record is currently active and approved.
     */
    public function isActive(): bool
    {
        return $this->status_approval === 'Approved'
            && $this->borrowed_at !== null
            && $this->expired_at !== null
            && now()->between($this->borrowed_at, $this->expired_at);
    }
}
