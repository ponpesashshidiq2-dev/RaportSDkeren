<?php

namespace App\Http\Controllers;

use App\Models\Classroom;
use App\Models\LearningObjective;
use App\Models\ReportSetting;
use App\Models\Subject;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class LearningObjectiveController extends Controller
{
    public function index(Request $request): View
    {
        $settings = ReportSetting::current();

        $filters = [
            'subject_id' => $request->integer('subject_id') ?: null,
            'classroom_id' => $request->integer('classroom_id') ?: null,
            'tahun_ajaran' => (string) ($request->input('tahun_ajaran') ?: $settings->active_academic_year),
            'semester' => (string) ($request->input('semester') ?: $settings->active_semester),
        ];

        $objectives = LearningObjective::query()
            ->with(['subject', 'classroom'])
            ->when($filters['subject_id'], fn ($q) => $q->where('subject_id', $filters['subject_id']))
            ->when($filters['classroom_id'], fn ($q) => $q->where('classroom_id', $filters['classroom_id']))
            ->where('academic_year', $filters['tahun_ajaran'])
            ->where('semester', $filters['semester'])
            ->orderBy('subject_id')
            ->orderBy('order_no')
            ->get();

        return view('learning-objectives.index', [
            'objectives' => $objectives,
            'filters' => $filters,
            'subjects' => Subject::query()->where('is_active', true)->orderBy('name')->get(),
            'classrooms' => Classroom::query()->orderBy('grade')->orderBy('name')->get(),
            'academicYears' => $this->academicYears(),
            'settings' => $settings,
        ]);
    }

    public function create(Request $request): View
    {
        $settings = ReportSetting::current();

        return view('learning-objectives.form', [
            'objective' => new LearningObjective([
                'academic_year' => (string) ($request->input('tahun_ajaran') ?: $settings->active_academic_year),
                'semester' => (string) ($request->input('semester') ?: $settings->active_semester),
                'classroom_id' => $request->integer('classroom_id') ?: null,
                'subject_id' => $request->integer('subject_id') ?: null,
            ]),
            'subjects' => Subject::query()->where('is_active', true)->orderBy('name')->get(),
            'classrooms' => Classroom::query()->orderBy('grade')->orderBy('name')->get(),
            'academicYears' => $this->academicYears(),
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        LearningObjective::create($this->validated($request));

        return redirect()
            ->route('learning-objectives.index', $request->only('subject_id', 'classroom_id', 'tahun_ajaran', 'semester'))
            ->with('success', 'Tujuan Pembelajaran berhasil ditambahkan.');
    }

    public function edit(LearningObjective $learningObjective): View
    {
        return view('learning-objectives.form', [
            'objective' => $learningObjective,
            'subjects' => Subject::query()->where('is_active', true)->orderBy('name')->get(),
            'classrooms' => Classroom::query()->orderBy('grade')->orderBy('name')->get(),
            'academicYears' => $this->academicYears(),
        ]);
    }

    public function update(Request $request, LearningObjective $learningObjective): RedirectResponse
    {
        $learningObjective->update($this->validated($request, $learningObjective));

        return redirect()
            ->route('learning-objectives.index', $request->only('subject_id', 'classroom_id', 'tahun_ajaran', 'semester'))
            ->with('success', 'Tujuan Pembelajaran berhasil diperbarui.');
    }

    public function destroy(LearningObjective $learningObjective): RedirectResponse
    {
        $learningObjective->delete();

        return redirect()->route('learning-objectives.index')->with('success', 'TP berhasil dihapus.');
    }

    /**
     * @return array<string, mixed>
     */
    private function validated(Request $request, ?LearningObjective $objective = null): array
    {
        return $request->validate([
            'subject_id' => ['required', 'exists:subjects,id'],
            'classroom_id' => ['required', 'exists:classrooms,id'],
            'tahun_ajaran' => ['required', 'string', 'max:20'],
            'semester' => ['required', 'in:1,2'],
            'code' => ['required', 'string', 'max:30'],
            'description' => ['required', 'string'],
            'approach' => ['nullable', 'string'],
            'order_no' => ['nullable', 'integer', 'min:0'],
        ], [
            'code.unique' => 'Kode TP tersebut sudah dipakai pada mapel, kelas, dan periode yang sama.',
        ]);
    }

    /**
     * @return array<int, string>
     */
    private function academicYears(): array
    {
        $current = (int) date('Y');

        $years = [];
        for ($y = $current + 1; $y >= $current - 4; $y--) {
            $years[] = ($y - 1).'/'.$y;
        }

        return $years;
    }
}
