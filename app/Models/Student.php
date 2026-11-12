<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
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
        'created_by',
        'is_active',
    ];

    protected $casts = [
        'tanggal_lahir' => 'date',
        'is_active' => 'boolean',
    ];

    protected $appends = ['ttl'];

    /**
     * Siswa yang boleh dilihat/dikelola oleh user tertentu:
     * - Super Admin & Kepala Sekolah: semua siswa
     * - Siswa/Orang Tua: hanya miliknya sendiri
     * - Guru & Wali Kelas: hanya siswa yang dibuat/diimport sendiri (created_by)
     *
     * Fail-closed: user null = tidak terlihat sama sekali.
     */
    public function scopeVisibleTo(Builder $query, ?User $user): Builder
    {
        if ($user === null) {
            return $query->whereRaw('1 = 0');
        }

        return match ($user->role) {
            User::ROLE_SUPER_ADMIN, User::ROLE_KEPALA_SEKOLAH => $query,
            User::ROLE_SISWA_ORANGTUA => $query->where('id', $user->student_id),
            default => $query->where('created_by', $user->id),
        };
    }

    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

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
