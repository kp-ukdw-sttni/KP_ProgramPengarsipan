<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class KategoriArsip extends Model
{
    use HasFactory;

    protected $table = 'kategori_arsip';

    protected $fillable = ['name', 'kode', 'deskripsi', 'parent_id'];

    /**
     * Get the archives belonging to this category.
     */
    public function arsip(): HasMany
    {
        return $this->hasMany(Arsip::class, 'kategori_id');
    }

    /**
     * Get the parent category.
     */
    public function parent()
    {
        return $this->belongsTo(KategoriArsip::class, 'parent_id');
    }

    /**
     * Get the sub-categories.
     */
    public function children()
    {
        return $this->hasMany(KategoriArsip::class, 'parent_id');
    }
}
