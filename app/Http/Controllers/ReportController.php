<?php

namespace App\Http\Controllers;

use App\Models\Classroom;
use App\Models\ReportSetting;
use App\Models\Student;
use App\Models\Subject;
use App\Services\ReportService;
use App\Services\ScoreService;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class ReportController extends Controller
{
    public function __construct(
        private ReportService $reportService,
        private ScoreService $scoreService,
    ) {}

    /**
     * Pilihan kelas/periode untuk cetak & rekap.
     */
    public function index(Request $request): View|RedirectResponse
    {
        $user = $request->user();

        // Siswa/Orang Tua: langsung menuju rapor miliknya sendiri.
        if ($user->role === $user::ROLE_SISWA_ORANGTUA) {
            if ($user->student_id) {
                return redirect()->route('reports.show', ['student_id' => $user->student_id]);
            }

            return back()->with('error', 'Akun Anda belum terhubung dengan data siswa.');
        }

        $settings = ReportSetting::current();

        $students = collect();
        $classroom = $request->filled('classroom_id')
            ? Classroom::find($request->integer('classroom_id'))
            : null;

        if ($classroom) {
            $students = Student::query()
                ->where('classroom_id', $classroom->id)
                ->where('is_active', true)
                ->orderBy('nama')
                ->get();
        }

        return view('reports.index', [
            'classrooms' => Classroom::query()->orderBy('grade')->orderBy('name')->get(),
            'students' => $students,
            'classroom' => $classroom,
            'year' => (string) ($request->input('tahun_ajaran') ?: $settings->active_academic_year),
            'semester' => (string) ($request->input('semester') ?: $settings->active_semester),
            'settings' => $settings,
        ]);
    }

    /**
     * Rekap nilai akhir per kelas + warna MERAH untuk nilai yang turun
     * dibandingkan semester/tahun ajaran sebelumnya.
     */
    public function recap(Request $request): View
    {
        $settings = ReportSetting::current();

        $classroom = $request->filled('classroom_id')
            ? Classroom::find($request->integer('classroom_id'))
            : null;

        $subject = $request->filled('subject_id')
            ? Subject::find($request->integer('subject_id'))
            : null;

        $year = (string) ($request->input('tahun_ajaran') ?: $settings->active_academic_year);
        $semester = (string) ($request->input('semester') ?: $settings->active_semester);

        $recap = $classroom
            ? $this->scoreService->recapWithComparison($classroom, $year, $semester, $subject)
            : collect();

        return view('reports.recap', [
            'classroom' => $classroom,
            'subject' => $subject,
            'year' => $year,
            'semester' => $semester,
            'recap' => $recap,
            'classrooms' => Classroom::query()->orderBy('grade')->orderBy('name')->get(),
            'subjects' => Subject::query()->where('is_active', true)->orderBy('name')->get(),
            'settings' => $settings,
        ]);
    }

    /**
     * Preview rapor di layar (halaman A4).
     */
    public function show(Request $request): View
    {
        $student = $this->resolveStudent($request);

        $data = $this->reportService->build(
            $student,
            (string) $this->resolveYear($request),
            (string) $this->resolveSemester($request),
        );

        return view('reports.show', $data);
    }

    /**
     * Cetak/unduh PDF rapor (A4) via dompdf.
     */
    public function printPdf(Request $request)
    {
        $student = $this->resolveStudent($request);

        $data = $this->reportService->build(
            $student,
            (string) $this->resolveYear($request),
            (string) $this->resolveSemester($request),
        );

        $filename = sprintf(
            'rapor-%s-%s-semester-%s.pdf',
            preg_replace('/[^A-Za-z0-9]+/', '-', $student->nama),
            $data['identity']['academic_year'],
            $data['identity']['semester_label'] === 'Semester 1 (Ganjil)' ? '1' : '2'
        );

        return Pdf::loadView('reports.print', $data)
            ->setPaper('a4', 'portrait')
            ->download($filename);
    }

    private function resolveStudent(Request $request): Student
    {
        $user = $request->user();

        if ($user->role === $user::ROLE_SISWA_ORANGTUA) {
            abort_unless($user->student_id, 403, 'Akun belum terhubung dengan data siswa.');

            return Student::query()->findOrFail($user->student_id);
        }

        $request->validate(['student_id' => ['required', 'exists:students,id']]);

        return Student::query()->with('classroom.waliKelas')->findOrFail($request->integer('student_id'));
    }

    private function resolveYear(Request $request): string
    {
        return (string) ($request->input('tahun_ajaran') ?: ReportSetting::current()->active_academic_year);
    }

    private function resolveSemester(Request $request): string
    {
        return (string) ($request->input('semester') ?: ReportSetting::current()->active_semester);
    }
}
