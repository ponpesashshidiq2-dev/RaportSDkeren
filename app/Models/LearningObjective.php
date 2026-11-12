<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class LearningObjective extends Model
{
    protected $fillable = [
        'subject_id',
        'classroom_id',
        'academic_year',
        'semester',
        'code',
        'description',
        'approach',
        'order_no',
    ];

    protected $casts = [
        'order_no' => 'integer',
    ];

    public function subject(): BelongsTo
    {
        return $this->belongsTo(Subject::class);
    }

    public function classroom(): BelongsTo
    {
        return $this->belongsTo(Classroom::class);
    }

    public function formativeScores(): HasMany
    {
        return $this->hasMany(FormativeScore::class);
    }
}
