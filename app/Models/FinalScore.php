<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class FinalScore extends Model
{
    protected $table = 'final_scores';

    protected $fillable = [
        'student_id',
        'subject_id',
        'classroom_id',
        'academic_year',
        'semester',
        'avg_formatif',
        'avg_sumatif',
        'final_score',
        'previous_score',
        'is_decreased',
        'calculated_at',
    ];

    protected $casts = [
        'avg_formatif' => 'float',
        'avg_sumatif' => 'float',
        'final_score' => 'float',
        'previous_score' => 'float',
        'is_decreased' => 'boolean',
        'calculated_at' => 'datetime',
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
