<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class KategoriArsip extends Model
{
    use HasFactory;

    protected $table = 'kategori_arsip';

    protected $fillable = ['name'];

    /**
     * Get the archives belonging to this category.
     */
    public function arsip(): HasMany
    {
        return $this->hasMany(Arsip::class, 'kategori_id');
    }
}
