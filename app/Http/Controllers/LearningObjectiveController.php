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
    public function index(Request $request): View|RedirectResponse
    {
        $settings = ReportSetting::current();

        // Kelas aktif hanya diatur di halaman Pengaturan.
        $classroom = $request->user()->classroom;

        if (! $classroom) {
            return redirect()->route('settings.show')->with('error', 'Atur kelas aktif Anda terlebih dahulu di menu Pengaturan.');
        }

        $filters = [
            'subject_id' => $request->integer('subject_id') ?: null,
            'classroom_id' => $classroom->id,
            'tahun_ajaran' => (string) ($request->input('tahun_ajaran') ?: $settings->active_academic_year),
            'semester' => (string) ($request->input('semester') ?: $settings->active_semester),
        ];

        $objectives = LearningObjective::query()
            ->with(['subject', 'classroom'])
            ->when($filters['subject_id'], fn ($q) => $q->where('subject_id', $filters['subject_id']))
            ->where('classroom_id', $classroom->id)
            ->where('academic_year', $filters['tahun_ajaran'])
            ->where('semester', $filters['semester'])
            ->orderBy('subject_id')
            ->orderBy('order_no')
            ->get();

        return view('learning-objectives.index', [
            'objectives' => $objectives,
            'filters' => $filters,
            'classroom' => $classroom,
            'subjects' => Subject::query()->where('is_active', true)->orderBy('name')->get(),
            'academicYears' => $this->academicYears(),
            'settings' => $settings,
        ]);
    }

    public function create(Request $request): View|RedirectResponse
    {
        $settings = ReportSetting::current();

        // Kelas TP baru selalu kelas aktif dari Pengaturan.
        $classroom = $request->user()->classroom;

        if (! $classroom) {
            return redirect()->route('settings.show')->with('error', 'Atur kelas aktif Anda terlebih dahulu di menu Pengaturan.');
        }

        return view('learning-objectives.form', [
            'objective' => new LearningObjective([
                'academic_year' => (string) ($request->input('tahun_ajaran') ?: $settings->active_academic_year),
                'semester' => (string) ($request->input('semester') ?: $settings->active_semester),
                'classroom_id' => $classroom->id,
                'subject_id' => $request->integer('subject_id') ?: null,
            ]),
            'subjects' => Subject::query()->where('is_active', true)->orderBy('name')->get(),
            'classrooms' => Classroom::query()->orderBy('grade')->orderBy('name')->get(),
            'academicYears' => $this->academicYears(),
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $data = $this->validated($request);

        // Kelas TP mengikuti kelas aktif pengguna (diatur di Pengaturan).
        $classroom = $request->user()->classroom;

        if (! $classroom) {
            return redirect()->route('settings.show')->with('error', 'Atur kelas aktif Anda terlebih dahulu di menu Pengaturan.');
        }

        $data['classroom_id'] = $classroom->id;

        LearningObjective::create($data);

        return redirect()
            ->route('learning-objectives.index', $request->only('subject_id', 'tahun_ajaran', 'semester'))
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
        $data = $this->validated($request, $learningObjective);

        // Kelas TP tidak diubah dari sini — pergantian kelas dilakukan via Pengaturan
        // dan hanya mempengaruhi kelas aktif, bukan data TP yang sudah ada.
        unset($data['classroom_id']);

        $learningObjective->update($data);

        return redirect()
            ->route('learning-objectives.index', $request->only('subject_id', 'tahun_ajaran', 'semester'))
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
