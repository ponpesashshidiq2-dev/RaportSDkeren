<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class SummativeScore extends Model
{
    protected $fillable = [
        'student_id',
        'subject_id',
        'classroom_id',
        'academic_year',
        'semester',
        'lingkup_materi',
        'score',
        'assessed_at',
    ];

    protected $casts = [
        'score' => 'float',
        'assessed_at' => 'date',
    ];

    public function student(): BelongsTo
    {
        return $this->belongsTo(Student::class);
    }

    public function subject(): BelongsTo
    {
        return $this->belongsTo(Subject::class);
    }

    public function classroom(): BelongsTo
    {
        return $this->belongsTo(Classroom::class);
    }
}
