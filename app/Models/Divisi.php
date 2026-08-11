<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Divisi extends Model
{
    use HasFactory;

    protected $table = 'divisi';

    protected $fillable = ['name', 'kode', 'parent_id'];

    /**
     * Get the users belonging to this division.
     */
    public function users(): HasMany
    {
        return $this->hasMany(User::class, 'divisi_id');
    }

    /**
     * Get the archives belonging to this division.
     */
    public function arsip(): HasMany
    {
        return $this->hasMany(Arsip::class, 'divisi_id');
    }

    /**
     * Get the parent (Fakultas) of this division.
     */
    public function parent(): BelongsTo
    {
        return $this->belongsTo(Divisi::class, 'parent_id');
    }

    /**
     * Get the child (Prodi) divisions under this Fakultas.
     */
    public function children(): HasMany
    {
        return $this->hasMany(Divisi::class, 'parent_id');
    }

    /**
     * Determine whether this divisi is a top-level (Fakultas).
     */
    public function isFakultas(): bool
    {
        return $this->parent_id === null;
    }
}
