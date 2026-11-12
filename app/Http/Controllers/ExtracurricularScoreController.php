<?php

namespace App\Http\Controllers;

use App\Models\Classroom;
use App\Models\Extracurricular;
use App\Models\ExtracurricularScore;
use App\Models\ReportSetting;
use App\Models\Student;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class ExtracurricularScoreController extends Controller
{
    public function index(Request $request): View|RedirectResponse
    {
        [$classroom, $extracurricular, $year, $semester] = $this->resolveContext($request);

        if (! $classroom) {
            return redirect()->route('settings.show')->with('error', 'Atur kelas aktif Anda terlebih dahulu di menu Pengaturan.');
        }

        $students = Student::query()->visibleTo($request->user())->where('classroom_id', $classroom->id)->where('is_active', true)->orderBy('nama')->get();

        $scores = ($students->isNotEmpty() && $extracurricular)
            ? ExtracurricularScore::query()
                ->where('extracurricular_id', $extracurricular->id)
                ->where('academic_year', $year)
                ->where('semester', $semester)
                ->whereIn('student_id', $students->pluck('id'))
                ->get()
                ->keyBy('student_id')
            : collect();

        return view('scores.extracurricular', [
            'classroom' => $classroom,
            'extracurricular' => $extracurricular,
            'year' => $year,
            'semester' => $semester,
            'students' => $students,
            'scores' => $scores,
            'extracurricularList' => Extracurricular::query()->where('is_active', true)->orderBy('name')->get(),
            'settings' => ReportSetting::current(),
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'extracurricular_id' => ['required', 'exists:extracurriculars,id'],
            'tahun_ajaran' => ['required', 'string', 'max:20'],
            'semester' => ['required', 'in:1,2'],
            'rows' => ['nullable', 'array'],
            'rows.*' => ['nullable', 'string', 'max:50'],
        ]);

        $classroom = $request->user()->classroom;

        if (! $classroom) {
            return redirect()->route('settings.show')->with('error', 'Atur kelas aktif Anda terlebih dahulu di menu Pengaturan.');
        }

        $extracurricular = Extracurricular::findOrFail($data['extracurricular_id']);

        foreach (($data['rows'] ?? []) as $studentId => $predikat) {
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
                'extracurricular_id' => $extracurricular->id,
                'academic_year' => $data['tahun_ajaran'],
                'semester' => $data['semester'],
            ];

            $predikat = is_string($predikat) ? trim($predikat) : '';

            if ($predikat === '') {
                // Kosong = nilai ekskul tidak diinput -> bagian ekskul tidak muncul di rapor
                ExtracurricularScore::query()->where($attributes)->delete();

                continue;
            }

            ExtracurricularScore::query()->updateOrCreate($attributes, [
                'classroom_id' => $classroom->id,
                'predikat' => $predikat,
            ]);
        }

        return redirect()
            ->route('scores.extracurricular', $request->only('extracurricular_id', 'tahun_ajaran', 'semester'))
            ->with('success', 'Nilai ekstrakurikuler tersimpan.');
    }

    /**
     * @return array{0:?Classroom,1:?Extracurricular,2:string,3:string}
     */
    private function resolveContext(Request $request): array
    {
        $settings = ReportSetting::current();

        // Kelas aktif hanya diatur di halaman Pengaturan.
        $classroom = $request->user()->classroom;

        $extracurricular = $request->filled('extracurricular_id')
            ? Extracurricular::find($request->integer('extracurricular_id'))
            : null;

        $year = (string) ($request->input('tahun_ajaran') ?: $settings->active_academic_year);
        $semester = (string) ($request->input('semester') ?: $settings->active_semester);

        return [$classroom, $extracurricular, $year, $semester];
    }
}
