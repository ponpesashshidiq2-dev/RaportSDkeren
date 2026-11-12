<?php

namespace App\Services;

use App\Models\FinalScore;
use App\Models\Student;
use App\Models\Subject;

/**
 * Logika lintas semester/tahun ajaran:
 * - Menentukan periode sebelumnya dari (semester, tahun ajaran).
 * - Mendeteksi penurunan nilai akhir siswa dibandingkan periode sebelumnya.
 *   Jika menurun, baris/angka nilai diberi TANDA MERAH pada rekap & pengolahan nilai.
 */
class ScoreComparisonService
{
    /**
     * Periode sebelumnya:
     *  - Semester 2 (Genap)  -> Semester 1 tahun ajaran yang sama.
     *  - Semester 1 (Ganjil) -> Semester 2 tahun ajaran sebelumnya (mis. 2025/2026 -> 2024/2025).
     *
     * @return array{0:string,1:string}|null [semester, tahun_ajaran]
     */
    public function previousPeriod(string $semester, string $academicYear): ?array
    {
        $semester = (string) $semester;

        if ($semester === '2') {
            return ['1', $academicYear];
        }

        if ($semester === '1') {
            $parts = explode('/', $academicYear);
            if (count($parts) === 2 && is_numeric($parts[0]) && is_numeric($parts[1])) {
                return ['2', ($parts[0] - 1).'/'.($parts[1] - 1)];
            }
        }

        return null;
    }

    /**
     * Nilai akhir siswa pada periode tertentu (null jika belum ada).
     */
    public function scoreAt(Student $student, Subject $subject, string $semester, string $academicYear): ?float
    {
        $score = FinalScore::query()
            ->where('student_id', $student->id)
            ->where('subject_id', $subject->id)
            ->where('semester', $semester)
            ->where('academic_year', $academicYear)
            ->value('final_score');

        return $score === null ? null : (float) $score;
    }

    /**
     * Apakah nilai akhir periode ini TURUN dibanding periode sebelumnya?
     */
    public function isDecreased(Student $student, Subject $subject, string $semester, string $academicYear): bool
    {
        $prev = $this->previousPeriod($semester, $academicYear);

        if (! $prev) {
            return false;
        }

        $current = $this->scoreAt($student, $subject, $semester, $academicYear);
        $previous = $this->scoreAt($student, $subject, $prev[0], $prev[1]);

        if ($current === null || $previous === null) {
            return false;
        }

        return $current < $previous;
    }

    /**
     * Ambil nilai akhir periode sebelumnya (untuk ditampilkan di rekap).
     */
    public function previousScore(?float $current, Student $student, Subject $subject, string $semester, string $academicYear): ?float
    {
        $prev = $this->previousPeriod($semester, $academicYear);

        if (! $prev) {
            return null;
        }

        return $this->scoreAt($student, $subject, $prev[0], $prev[1]);
    }

    /**
     * Tandai/muat ulang kolom previous_score & is_decreased pada tabel final_scores.
     * Dipanggil setelah kalkulasi nilai akhir.
     */
    public function refreshFlags(Student $student, Subject $subject, string $semester, string $academicYear): FinalScore|null
    {
        $final = FinalScore::query()
            ->where('student_id', $student->id)
            ->where('subject_id', $subject->id)
            ->where('semester', $semester)
            ->where('academic_year', $academicYear)
            ->first();

        if (! $final) {
            return null;
        }

        $prev = $this->previousPeriod($semester, $academicYear);
        $previousScore = $prev ? $this->scoreAt($student, $subject, $prev[0], $prev[1]) : null;

        $final->previous_score = $previousScore;
        $final->is_decreased = $previousScore !== null
            && $final->final_score !== null
            && $final->final_score < $previousScore;
        $final->save();

        return $final;
    }
}
