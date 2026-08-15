<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class StudyProgram extends Model
{
    use HasFactory;

    protected $table = 'study_programs';

    protected $fillable = ['name', 'kode'];

    /**
     * Get the archives grouped under this study program.
     */
    public function arsip(): HasMany
    {
        return $this->hasMany(Arsip::class, 'study_program_id');
    }
}
