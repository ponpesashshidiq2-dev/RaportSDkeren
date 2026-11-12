<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Classroom extends Model
{
    protected $fillable = [
        'name',
        'grade',
        'academic_year',
        'wali_kelas_id',
    ];

    protected $casts = [
        'grade' => 'integer',
    ];

    public function waliKelas(): BelongsTo
    {
        return $this->belongsTo(User::class, 'wali_kelas_id');
    }

    public function students(): HasMany
    {
        return $this->hasMany(Student::class)->orderBy('nama');
    }

    public function learningObjectives(): HasMany
    {
        return $this->hasMany(LearningObjective::class);
    }

    public function label(): string
    {
        return 'Kelas '.$this->name;
    }
}
