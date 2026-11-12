<?php

namespace App\Http\Controllers;

use App\Models\Classroom;
use App\Models\LoginLog;
use App\Models\ReportSetting;
use App\Models\Student;
use App\Models\Subject;
use App\Models\User;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;

class DashboardController extends Controller
{
    public function __invoke(): View
    {
        $user = Auth::user();
        $settings = ReportSetting::current();

        $stats = [
            'siswa' => Student::query()->where('is_active', true)->count(),
            'guru' => User::query()->whereIn('role', [
                User::ROLE_GURU,
                User::ROLE_WALI_KELAS,
                User::ROLE_KEPALA_SEKOLAH,
            ])->count(),
            'kelas' => Classroom::query()->count(),
            'mapel' => Subject::query()->where('is_active', true)->count(),
            'login_hari_ini' => LoginLog::query()->whereDate('logged_in_at', today())->count(),
            'login_total' => LoginLog::query()->count(),
        ];

        $recentLogins = LoginLog::query()
            ->with('user')
            ->latest('logged_in_at')
            ->limit(8)
            ->get();

        $siswaPerKelas = Classroom::query()
            ->withCount(['students' => fn ($q) => $q->where('is_active', true)])
            ->orderBy('grade')
            ->orderBy('name')
            ->get();

        return view('dashboard', compact('user', 'settings', 'stats', 'recentLogins', 'siswaPerKelas'));
    }
}
