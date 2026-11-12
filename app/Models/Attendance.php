<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Attendance extends Model
{
    protected $fillable = [
        'student_id',
        'classroom_id',
        'academic_year',
        'semester',
        'sakit',
        'izin',
        'tanpa_keterangan',
    ];

    protected $casts = [
        'sakit' => 'integer',
        'izin' => 'integer',
        'tanpa_keterangan' => 'integer',
    ];

    public function student(): BelongsTo
    {
        return $this->belongsTo(Student::class);
    }

    public function classroom(): BelongsTo
    {
        return $this->belongsTo(Classroom::class);
    }
}
