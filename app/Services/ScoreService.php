<?php

namespace App\Services;

use App\Models\Classroom;
use App\Models\FinalScore;
use App\Models\FormativeScore;
use App\Models\ReportSetting;
use App\Models\Student;
use App\Models\Subject;
use App\Models\SummativeScore;
use Illuminate\Support\Collection;

/**
 * Kalkulasi nilai akhir:
 *   Nilai Akhir = (Rata-rata Formatif x Bobot Formatif) + (Rata-rata Sumatif x Bobot Sumatif)
 * Bobot diambil dari report_settings (default 40% formatif / 60% sumatif).
 * Jika salah satu komponen belum ada, nilai akhir memakai komponen yang tersedia.
 */
class ScoreService
{
    public function __construct(private ScoreComparisonService $comparison) {}

    /** @return array{formatif:float,sumatif:float} bobot dalam persen */
    public function weights(): array
    {
        $settings = ReportSetting::current();

        return [
            'formatif' => (float) $settings->formatif_weight,
            'sumatif' => (float) $settings->sumatif_weight,
        ];
    }

    /**
     * Hitung & simpan (serta update bendera penurunan) nilai akhir satu siswa-mapel.
     */
    public function calculateFinal(Student $student, Subject $subject, string $academicYear, string $semester): ?FinalScore
    {
        $avgFormatif = FormativeScore::query()
            ->where('student_id', $student->id)
            ->where('subject_id', $subject->id)
            ->where('academic_year', $academicYear)
            ->where('semester', $semester)
            ->avg('score');

        $avgSumatif = SummativeScore::query()
            ->where('student_id', $student->id)
            ->where('subject_id', $subject->id)
            ->where('academic_year', $academicYear)
            ->where('semester', $semester)
            ->avg('score');

        if ($avgFormatif === null && $avgSumatif === null) {
            FinalScore::query()
                ->where('student_id', $student->id)
                ->where('subject_id', $subject->id)
                ->where('academic_year', $academicYear)
                ->where('semester', $semester)
                ->delete();

            return null;
        }

        $w = $this->weights();
        $wFormatif = $w['formatif'] / 100;
        $wSumatif = $w['sumatif'] / 100;

        if ($avgFormatif !== null && $avgSumatif !== null) {
            $total = $wFormatif + $wSumatif;
            $final = $total > 0
                ? (($avgFormatif * $wFormatif) + ($avgSumatif * $wSumatif)) / $total
                : $avgFormatif;
        } elseif ($avgFormatif !== null) {
            $final = $avgFormatif;
        } else {
            $final = $avgSumatif;
        }

        $record = FinalScore::query()->updateOrCreate(
            [
                'student_id' => $student->id,
                'subject_id' => $subject->id,
                'academic_year' => $academicYear,
                'semester' => $semester,
            ],
            [
                'classroom_id' => $student->classroom_id,
                'avg_formatif' => $avgFormatif === null ? null : round($avgFormatif, 2),
                'avg_sumatif' => $avgSumatif === null ? null : round($avgSumatif, 2),
                'final_score' => round($final, 2),
                'calculated_at' => now(),
            ]
        );

        $this->comparison->refreshFlags($student, $subject, $semester, $academicYear);

        return $record->refresh();
    }

    /**
     * Kalkulasi ulang massal untuk satu kelas & periode.
     */
    public function recalculateClassroom(Classroom $classroom, string $academicYear, string $semester): int
    {
        $students = Student::query()
            ->where('classroom_id', $classroom->id)
            ->where('is_active', true)
            ->get();

        $subjects = Subject::query()
            ->where('is_active', true)
            ->get();

        $count = 0;
        foreach ($students as $student) {
            foreach ($subjects as $subject) {
                if ($this->calculateFinal($student, $subject, $academicYear, $semester)) {
                    $count++;
                }
            }
        }

        return $count;
    }

    /**
     * Rekap nilai akhir satu kelas + penanda penurunan (merah) dari periode sebelumnya.
     *
     * @return Collection<int, object{student:Student, subjects:Collection<int, object{subject:Subject, final:?float, previous:?float, decreased:bool}>}>
     */
    public function recapWithComparison(Classroom $classroom, string $academicYear, string $semester, ?Subject $subjectFilter = null): Collection
    {
        $students = Student::query()
            ->where('classroom_id', $classroom->id)
            ->where('is_active', true)
            ->orderBy('nama')
            ->get();

        $subjects = Subject::query()
            ->where('is_active', true)
            ->when($subjectFilter, fn ($q) => $q->where('id', $subjectFilter->id))
            ->orderByRaw("CASE muatan WHEN 'agama' THEN 1 WHEN 'nasional' THEN 2 WHEN 'lokal' THEN 3 ELSE 4 END")
            ->orderBy('name')
            ->get();

        return $students->map(function (Student $student) use ($subjects, $academicYear, $semester) {
            $subjectRows = $subjects->map(function (Subject $subject) use ($student, $academicYear, $semester) {
                $current = $this->comparison->scoreAt($student, $subject, $semester, $academicYear);
                $previous = $this->comparison->previousScore($current, $student, $subject, $semester, $academicYear);

                return (object) [
                    'subject' => $subject,
                    'final' => $current,
                    'previous' => $previous,
                    'decreased' => $previous !== null && $current !== null && $current < $previous,
                ];
            });

            return (object) [
                'student' => $student,
                'subjects' => $subjectRows,
            ];
        });
    }
}
