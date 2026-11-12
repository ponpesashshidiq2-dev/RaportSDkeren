<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

class User extends Authenticatable
{
    use HasFactory, Notifiable;

    public const ROLE_SUPER_ADMIN = 'super_admin';
    public const ROLE_GURU = 'guru';
    public const ROLE_WALI_KELAS = 'wali_kelas';
    public const ROLE_KEPALA_SEKOLAH = 'kepala_sekolah';
    public const ROLE_SISWA_ORANGTUA = 'siswa_orangtua';

    protected $fillable = [
        'name',
        'email',
        'password',
        'phone',
        'role',
        'classroom_id',
        'student_id',
    ];

    protected $hidden = [
        'password',
        'remember_token',
    ];

    protected $casts = [
        'email_verified_at' => 'datetime',
        'password' => 'hashed',
    ];

    public function classroom(): BelongsTo
    {
        return $this->belongsTo(Classroom::class);
    }

    public function student(): BelongsTo
    {
        return $this->belongsTo(Student::class);
    }

    public function loginLogs()
    {
        return $this->hasMany(LoginLog::class);
    }

    public function isOneOf(string ...$roles): bool
    {
        return in_array($this->role, $roles, true);
    }

    public function isStaff(): bool
    {
        return $this->isOneOf(
            self::ROLE_SUPER_ADMIN,
            self::ROLE_GURU,
            self::ROLE_WALI_KELAS,
            self::ROLE_KEPALA_SEKOLAH,
        );
    }

    public function roleLabel(): string
    {
        return match ($this->role) {
            self::ROLE_SUPER_ADMIN => 'Super Admin',
            self::ROLE_GURU => 'Guru',
            self::ROLE_WALI_KELAS => 'Wali Kelas',
            self::ROLE_KEPALA_SEKOLAH => 'Kepala Sekolah',
            self::ROLE_SISWA_ORANGTUA => 'Siswa / Orang Tua',
            default => ucfirst($this->role),
        };
    }

    /**
     * Bolehkah user ini melihat/mengelola satu siswa tertentu?
     * Aturan sama dengan Student::scopeVisibleTo().
     */
    public function canSeeStudent(?Student $student): bool
    {
        if ($student === null) {
            return false;
        }

        return match ($this->role) {
            self::ROLE_SUPER_ADMIN, self::ROLE_KEPALA_SEKOLAH => true,
            self::ROLE_SISWA_ORANGTUA => $this->student_id !== null
                && (int) $this->student_id === (int) $student->id,
            default => $student->created_by !== null
                && (int) $student->created_by === (int) $this->id,
        };
    }
}
