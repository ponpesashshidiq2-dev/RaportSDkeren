<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Subject extends Model
{
    public const MUATAN_AGAMA = 'agama';
    public const MUATAN_NASIONAL = 'nasional';
    public const MUATAN_LOKAL = 'lokal';

    protected $fillable = [
        'code',
        'name',
        'muatan',
        'kkm',
        'phase',
        'is_active',
    ];

    protected $casts = [
        'kkm' => 'float',
        'is_active' => 'boolean',
    ];

    public function learningObjectives(): HasMany
    {
        return $this->hasMany(LearningObjective::class);
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

    /**
     * Mapel dicetak di rapor hanya jika KKM diisi (tidak null).
     */
    public function scopePrintable($query)
    {
        return $query->whereNotNull('kkm');
    }

    public function muatanLabel(): string
    {
        return match ($this->muatan) {
            self::MUATAN_AGAMA => 'Muatan Agama',
            self::MUATAN_NASIONAL => 'Muatan Nasional',
            self::MUATAN_LOKAL => 'Muatan Lokal',
            default => ucfirst($this->muatan),
        };
    }

    public function getMuatanLabelAttribute(): string
    {
        return $this->muatanLabel();
    }
}
