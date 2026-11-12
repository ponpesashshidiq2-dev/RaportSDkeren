<?php

namespace App\Http\Controllers;

use App\Models\Attendance;
use App\Models\Classroom;
use App\Models\ReportSetting;
use App\Models\Student;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class AttendanceController extends Controller
{
    public function index(Request $request): View
    {
        $settings = ReportSetting::current();

        $classroom = $request->filled('classroom_id')
            ? Classroom::find($request->integer('classroom_id'))
            : null;

        $year = (string) ($request->input('tahun_ajaran') ?: $settings->active_academic_year);
        $semester = (string) ($request->input('semester') ?: $settings->active_semester);

        $students = $classroom
            ? Student::query()->where('classroom_id', $classroom->id)->where('is_active', true)->orderBy('nama')->get()
            : collect();

        $attendances = $students->isNotEmpty()
            ? Attendance::query()
                ->where('academic_year', $year)
                ->where('semester', $semester)
                ->whereIn('student_id', $students->pluck('id'))
                ->get()
                ->keyBy('student_id')
            : collect();

        return view('scores.attendance', [
            'classroom' => $classroom,
            'year' => $year,
            'semester' => $semester,
            'students' => $students,
            'attendances' => $attendances,
            'classrooms' => Classroom::query()->orderBy('grade')->orderBy('name')->get(),
            'settings' => $settings,
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'classroom_id' => ['required', 'exists:classrooms,id'],
            'tahun_ajaran' => ['required', 'string', 'max:20'],
            'semester' => ['required', 'in:1,2'],
            'rows' => ['nullable', 'array'],
            'rows.*.sakit' => ['nullable', 'integer', 'min:0', 'max:365'],
            'rows.*.izin' => ['nullable', 'integer', 'min:0', 'max:365'],
            'rows.*.tanpa_keterangan' => ['nullable', 'integer', 'min:0', 'max:365'],
        ]);

        $classroom = Classroom::findOrFail($data['classroom_id']);

        foreach (($data['rows'] ?? []) as $studentId => $row) {
            $student = Student::query()
                ->where('id', $studentId)
                ->where('classroom_id', $classroom->id)
                ->first();

            if (! $student) {
                continue;
            }

            Attendance::query()->updateOrCreate(
                [
                    'student_id' => $student->id,
                    'academic_year' => $data['tahun_ajaran'],
                    'semester' => $data['semester'],
                ],
                [
                    'classroom_id' => $classroom->id,
                    'sakit' => (int) ($row['sakit'] ?? 0),
                    'izin' => (int) ($row['izin'] ?? 0),
                    'tanpa_keterangan' => (int) ($row['tanpa_keterangan'] ?? 0),
                ]
            );
        }

        return redirect()
            ->route('scores.attendance', $request->only('classroom_id', 'tahun_ajaran', 'semester'))
            ->with('success', 'Data kehadiran siswa tersimpan.');
    }
}
