<?php

namespace App\Http\Controllers;

use App\Models\Classroom;
use App\Models\FormativeScore;
use App\Models\LearningObjective;
use App\Models\ReportSetting;
use App\Models\Student;
use App\Models\Subject;
use App\Services\ScoreService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class FormativeScoreController extends Controller
{
    public function __construct(private ScoreService $scoreService) {}

    public function index(Request $request): View
    {
        [$classroom, $subject, $year, $semester] = $this->resolveContext($request);

        $students = $classroom
            ? Student::query()->where('classroom_id', $classroom->id)->where('is_active', true)->orderBy('nama')->get()
            : collect();

        $objectives = ($classroom && $subject)
            ? LearningObjective::query()
                ->where('classroom_id', $classroom->id)
                ->where('subject_id', $subject->id)
                ->where('academic_year', $year)
                ->where('semester', $semester)
                ->orderBy('order_no')
                ->get()
            : collect();

        $scores = ($students->isNotEmpty() && $objectives->isNotEmpty())
            ? FormativeScore::query()
                ->where('academic_year', $year)
                ->where('semester', $semester)
                ->whereIn('student_id', $students->pluck('id'))
                ->whereIn('learning_objective_id', $objectives->pluck('id'))
                ->get()
                ->groupBy('student_id')
            : collect();

        return view('scores.formative', [
            'classroom' => $classroom,
            'subject' => $subject,
            'year' => $year,
            'semester' => $semester,
            'students' => $students,
            'objectives' => $objectives,
            'scores' => $scores,
            'classrooms' => Classroom::query()->orderBy('grade')->orderBy('name')->get(),
            'subjects' => Subject::query()->where('is_active', true)->orderBy('name')->get(),
            'settings' => ReportSetting::current(),
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'classroom_id' => ['required', 'exists:classrooms,id'],
            'subject_id' => ['required', 'exists:subjects,id'],
            'tahun_ajaran' => ['required', 'string', 'max:20'],
            'semester' => ['required', 'in:1,2'],
            'rows' => ['nullable', 'array'],
            'rows.*.*' => ['nullable', 'numeric', 'min:0', 'max:100'],
        ]);

        $classroom = Classroom::findOrFail($data['classroom_id']);
        $subject = Subject::findOrFail($data['subject_id']);

        $posted = $data['rows'] ?? [];

        foreach ($posted as $studentId => $tpScores) {
            $student = Student::query()
                ->where('id', $studentId)
                ->where('classroom_id', $classroom->id)
                ->first();

            if (! $student) {
                continue;
            }

            foreach ((array) $tpScores as $tpId => $score) {
                $objective = LearningObjective::query()
                    ->where('id', $tpId)
                    ->where('subject_id', $subject->id)
                    ->where('classroom_id', $classroom->id)
                    ->where('academic_year', $data['tahun_ajaran'])
                    ->where('semester', $data['semester'])
                    ->first();

                if (! $objective) {
                    continue;
                }

                $attributes = [
                    'student_id' => $student->id,
                    'learning_objective_id' => $objective->id,
                    'academic_year' => $data['tahun_ajaran'],
                    'semester' => $data['semester'],
                ];

                if ($score === null || $score === '') {
                    FormativeScore::query()->where($attributes)->delete();

                    continue;
                }

                FormativeScore::query()->updateOrCreate($attributes, [
                    'subject_id' => $subject->id,
                    'classroom_id' => $classroom->id,
                    'score' => (float) $score,
                    'assessed_at' => now()->toDateString(),
                ]);
            }

            $this->scoreService->calculateFinal($student, $subject, $data['tahun_ajaran'], $data['semester']);
        }

        return redirect()
            ->route('scores.formative', $request->only('classroom_id', 'subject_id', 'tahun_ajaran', 'semester'))
            ->with('success', 'Nilai formatif tersimpan. Nilai akhir & penanda penurunan telah diperbarui.');
    }

    /**
     * @return array{0:?Classroom,1:?Subject,2:string,3:string}
     */
    private function resolveContext(Request $request): array
    {
        $settings = ReportSetting::current();

        $classroom = $request->filled('classroom_id')
            ? Classroom::find($request->integer('classroom_id'))
            : null;

        $subject = $request->filled('subject_id')
            ? Subject::find($request->integer('subject_id'))
            : null;

        $year = $request->string('tahun_ajaran') ?: $settings->active_academic_year;
        $semester = $request->string('semester') ?: $settings->active_semester;

        return [$classroom, $subject, (string) $year, (string) $semester];
    }
}
