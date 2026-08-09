<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ArsipVersion extends Model
{
    use HasFactory;

    protected $table = 'arsip_versions';

    protected $fillable = [
        'arsip_id',
        'version_number',
        'file_path',
        'uploaded_by',
        'change_note',
    ];

    /**
     * Get the main archive that this version belongs to.
     */
    public function arsip(): BelongsTo
    {
        return $this->belongsTo(Arsip::class, 'arsip_id');
    }

    /**
     * Get the user who uploaded this version.
     */
    public function uploader(): BelongsTo
    {
        return $this->belongsTo(User::class, 'uploaded_by');
    }
}
