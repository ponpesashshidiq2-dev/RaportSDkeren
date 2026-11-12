<?php

namespace App\Http\Controllers;

use App\Models\Classroom;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

/**
 * Halaman Pengaturan — SATU-SATUNYA tempat pengguna mengatur kelas aktifnya.
 * Halaman Nilai, CP/TP, Data Siswa, dan Rapor memakai kelas dari sini otomatis
 * (tanpa pemilihan kelas di halaman tersebut).
 */
class SettingController extends Controller
{
    public function show(Request $request): View
    {
        $user = $request->user()->load(['classroom', 'student.classroom']);

        $classrooms = Classroom::query()
            ->orderBy('grade')
            ->orderBy('name')
            ->get();

        return view('settings.show', compact('user', 'classrooms'));
    }

    public function update(Request $request): RedirectResponse
    {
        $user = $request->user();

        abort_unless($user->isStaff(), 403, 'Hanya pengguna staf yang dapat mengatur kelas.');

        $data = $request->validate([
            'classroom_id' => ['required', 'exists:classrooms,id'],
        ], [
            'classroom_id.required' => 'Pilih kelas terlebih dahulu.',
        ]);

        $user->update(['classroom_id' => $data['classroom_id']]);

        return redirect()
            ->route('settings.show')
            ->with('success', 'Kelas aktif diperbarui. Halaman Nilai, CP/TP, Data Siswa, dan Rapor kini memakai kelas ini.');
    }
}
