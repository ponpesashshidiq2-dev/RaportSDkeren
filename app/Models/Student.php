<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Student extends Model
{
    protected $fillable = [
        'nisn',
        'nis',
        'nama',
        'jenis_kelamin',
        'tempat_lahir',
        'tanggal_lahir',
        'alamat',
        'no_hp',
        'nama_ayah',
        'nama_ibu',
        'nama_wali',
        'classroom_id',
        'photo',
        'is_active',
    ];

    protected $casts = [
        'tanggal_lahir' => 'date',
        'is_active' => 'boolean',
    ];

    protected $appends = ['ttl'];

    public function classroom(): BelongsTo
    {
        return $this->belongsTo(Classroom::class);
    }

    public function formativeScores(): HasMany
    {
        return $this->hasMany(FormativeScore::class);
    }

    public function summativeScores(): HasMany
    {
        return $this->hasMany(SummativeScore::class);
    }

    public function finalScores(): HasMany
    {
        return $this->hasMany(FinalScore::class);
    }

    public function extracurricularScores(): HasMany
    {
        return $this->hasMany(ExtracurricularScore::class);
    }

    public function attendances(): HasMany
    {
        return $this->hasMany(Attendance::class);
    }

    public function getTtlAttribute(): string
    {
        return sprintf('%s, %s', $this->tempat_lahir, optional($this->tanggal_lahir)->format('d-m-Y'));
    }

    public function getJenisKelaminLabelAttribute(): string
    {
        return $this->jenis_kelamin === 'L' ? 'Laki-laki' : 'Perempuan';
    }
}
