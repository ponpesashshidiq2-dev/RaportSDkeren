<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\View\View;

/**
 * Profil pengguna: data akun + data siswa (jika akun terhubung ke siswa).
 * Berlaku untuk semua role; data siswa hanya muncul bila user->student_id terisi.
 */
class ProfileController extends Controller
{
    public function show(Request $request): View
    {
        $user = $request->user()->load(['classroom', 'student.classroom']);

        $student = $user->student;
        $recentLogins = $user->loginLogs()
            ->latest('logged_in_at')
            ->limit(5)
            ->get();

        return view('profile.show', compact('user', 'student', 'recentLogins'));
    }
}
