<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Divisi extends Model
{
    use HasFactory;

    protected $table = 'divisi';

    protected $fillable = ['name'];

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
}
