<?php

namespace App\Http\Controllers;

use App\Models\Classroom;
use App\Models\ReportSetting;
use App\Models\Student;
use App\Models\Subject;
use App\Models\SummativeScore;
use App\Services\ScoreService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class SummativeScoreController extends Controller
{
    public function __construct(private ScoreService $scoreService) {}

    public function index(Request $request): View
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

        $students = $classroom
            ? Student::query()->visibleTo($request->user())->where('classroom_id', $classroom->id)->where('is_active', true)->orderBy('nama')->get()
            : collect();

        $lingkupOptions = ($subject)
            ? SummativeScore::query()
                ->where('subject_id', $subject->id)
                ->where('academic_year', $year)
                ->where('semester', $semester)
                ->pluck('lingkup_materi')
                ->unique()
                ->sort()
                ->values()
            : collect();

        $scores = ($students->isNotEmpty() && $subject)
            ? SummativeScore::query()
                ->where('subject_id', $subject->id)
                ->where('academic_year', $year)
                ->where('semester', $semester)
                ->whereIn('student_id', $students->pluck('id'))
                ->get()
                ->groupBy('student_id')
            : collect();

        return view('scores.summative', [
            'classroom' => $classroom,
            'subject' => $subject,
            'year' => $year,
            'semester' => $semester,
            'students' => $students,
            'scores' => $scores,
            'lingkupOptions' => $lingkupOptions,
            'classrooms' => Classroom::query()->orderBy('grade')->orderBy('name')->get(),
            'subjects' => Subject::query()->where('is_active', true)->orderBy('name')->get(),
            'settings' => $settings,
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'classroom_id' => ['required', 'exists:classrooms,id'],
            'subject_id' => ['required', 'exists:subjects,id'],
            'tahun_ajaran' => ['required', 'string', 'max:20'],
            'semester' => ['required', 'in:1,2'],
            'lingkup_materi' => ['required', 'string', 'max:255'],
            'rows' => ['nullable', 'array'],
            'rows.*' => ['nullable', 'numeric', 'min:0', 'max:100'],
        ]);

        $classroom = Classroom::findOrFail($data['classroom_id']);
        $subject = Subject::findOrFail($data['subject_id']);

        foreach (($data['rows'] ?? []) as $studentId => $score) {
            $student = Student::query()
                ->visibleTo($request->user())
                ->where('id', $studentId)
                ->where('classroom_id', $classroom->id)
                ->first();

            if (! $student) {
                continue;
            }

            $attributes = [
                'student_id' => $student->id,
                'subject_id' => $subject->id,
                'lingkup_materi' => $data['lingkup_materi'],
                'academic_year' => $data['tahun_ajaran'],
                'semester' => $data['semester'],
            ];

            if ($score === null || $score === '') {
                SummativeScore::query()->where($attributes)->delete();

                continue;
            }

            SummativeScore::query()->updateOrCreate($attributes, [
                'classroom_id' => $classroom->id,
                'score' => (float) $score,
                'assessed_at' => now()->toDateString(),
            ]);

            $this->scoreService->calculateFinal($student, $subject, $data['tahun_ajaran'], $data['semester']);
        }

        return redirect()
            ->route('scores.summative', $request->only('classroom_id', 'subject_id', 'tahun_ajaran', 'semester'))
            ->with('success', 'Nilai sumatif tersimpan. Nilai akhir & penanda penurunan telah diperbarui.');
    }
}
