<?php

namespace App\Http\Controllers;

use App\Models\Attendance;
use App\Models\ExtracurricularScore;
use App\Models\FinalScore;
use App\Models\FormativeScore;
use App\Models\ReportSetting;
use App\Models\Student;
use App\Models\Subject;
use App\Models\SummativeScore;
use App\Services\ScoreComparisonService;
use Illuminate\Http\Request;

/**
 * "Nilai Saya" — hanya menampilkan nilai milik user yang login (student_id sendiri).
 * Tidak menerima parameter student_id lain sehingga data tidak bisa diakses lintas user.
 */
class MyScoreController extends Controller
{
    public function __construct(private ScoreComparisonService $comparison) {}

    public function index(Request $request)
    {
        $user = $request->user();

        if (! $user->student_id) {
            return view('my-scores.index', [
                'student' => null,
                'error' => 'Akun ini belum terhubung dengan data siswa. Hubungi administrator sekolah.',
            ]);
        }

        $student = Student::query()->with('classroom')->findOrFail($user->student_id);
        $settings = ReportSetting::current();

        $year = (string) ($request->input('tahun_ajaran') ?: $settings->active_academic_year);
        $semester = (string) ($request->input('semester') ?: $settings->active_semester);

        // Daftar mapel yang dicetak di rapor (ber-KKM)
        $subjects = Subject::query()
            ->printable()
            ->where('is_active', true)
            ->orderByRaw("CASE muatan WHEN 'agama' THEN 1 WHEN 'nasional' THEN 2 WHEN 'lokal' THEN 3 ELSE 4 END")
            ->orderBy('name')
            ->get();

        $finals = FinalScore::query()
            ->where('student_id', $student->id)
            ->where('academic_year', $year)
            ->where('semester', $semester)
            ->get()
            ->keyBy('subject_id');

        $rows = $subjects->map(function (Subject $subject) use ($finals, $student, $year, $semester) {
            $final = $finals->get($subject->id);
            $current = $final?->final_score;
            $previous = $this->comparison->previousScore($current, $student, $subject, $semester, $year);
            $decreased = $previous !== null && $current !== null && $current < $previous;

            return (object) [
                'subject' => $subject,
                'final' => $current,
                'avg_formatif' => $final?->avg_formatif,
                'avg_sumatif' => $final?->avg_sumatif,
                'previous' => $previous,
                'decreased' => $decreased,
                'tuntas' => $current !== null && $subject->kkm !== null && $current >= $subject->kkm,
            ];
        });

        // Detail satu mapel (formatif per TP + sumatif)
        $detailSubject = $request->filled('subject_id')
            ? Subject::find($request->integer('subject_id'))
            : null;

        $formatifDetail = collect();
        $sumatifDetail = collect();

        if ($detailSubject) {
            $formatifDetail = FormativeScore::query()
                ->with('learningObjective')
                ->where('student_id', $student->id)
                ->where('subject_id', $detailSubject->id)
                ->where('academic_year', $year)
                ->where('semester', $semester)
                ->get()
                ->sortBy('learningObjective.order_no')
                ->values();

            $sumatifDetail = SummativeScore::query()
                ->where('student_id', $student->id)
                ->where('subject_id', $detailSubject->id)
                ->where('academic_year', $year)
                ->where('semester', $semester)
                ->orderBy('assessed_at')
                ->get();
        }

        $attendance = Attendance::query()
            ->where('student_id', $student->id)
            ->where('academic_year', $year)
            ->where('semester', $semester)
            ->first();

        $extracurriculars = ExtracurricularScore::query()
            ->with('extracurricular')
            ->where('student_id', $student->id)
            ->where('academic_year', $year)
            ->where('semester', $semester)
            ->whereNotNull('predikat')
            ->where('predikat', '<>', '')
            ->get();

        return view('my-scores.index', [
            'student' => $student,
            'error' => null,
            'year' => $year,
            'semester' => $semester,
            'rows' => $rows,
            'detailSubject' => $detailSubject,
            'formatifDetail' => $formatifDetail,
            'sumatifDetail' => $sumatifDetail,
            'attendance' => $attendance,
            'extracurriculars' => $extracurriculars,
            'settings' => $settings,
        ]);
    }
}
