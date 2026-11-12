<?php

namespace App\Services;

use App\Models\Attendance;
use App\Models\Classroom;
use App\Models\ExtracurricularScore;
use App\Models\FormativeScore;
use App\Models\LearningObjective;
use App\Models\ReportSetting;
use App\Models\Student;
use App\Models\Subject;
use Illuminate\Support\Collection;

/**
 * Pembangun data cetak Rapor:
 *  - Hanya mapel ber-KKM yang dicetak.
 *  - Capaian Kompetensi = 2 deskripsi TP (nilai tertinggi & terendah yang dicapai siswa).
 *  - Ekstrakurikuler dinamis (tidak muncul sama sekali jika tidak ada data nilai).
 *  - Rekap kehadiran Sakit | Izin | Tanpa Keterangan.
 *  - Tanda tangan: Wali Kelas, Orang Tua/Wali Murid, Kepala Sekolah.
 */
class ReportService
{
    public function __construct(private ScoreService $scores) {}

    /**
     * Fase Kurikulum Merdeka SD: A = kelas 1-2, B = 3-4, C = 5-6.
     */
    public function phaseFor(Classroom $classroom, ReportSetting $settings): string
    {
        return match (true) {
            $classroom->grade <= 2 => 'A',
            $classroom->grade <= 4 => 'B',
            default => 'C',
        } ?: $settings->default_phase;
    }

    public function semesterLabel(string $semester): string
    {
        return $semester === '1' ? 'Semester 1 (Ganjil)' : 'Semester 2 (Genap)';
    }

    /**
     * Susun seluruh data rapor satu siswa.
     *
     * @return array<string, mixed>
     */
    public function build(Student $student, string $academicYear, string $semester): array
    {
        $settings = ReportSetting::current();
        $classroom = $student->classroom;

        abort_unless($classroom !== null, 422, 'Siswa belum terdaftar pada kelas mana pun.');

        // 1. Identitas (hanya lembar pertama)
        $identity = [
            'student' => $student,
            'school_name' => $settings->school_name,
            'npsn' => $settings->npsn,
            'address' => $settings->address,
            'city' => $settings->city,
            'classroom' => $classroom,
            'phase' => $this->phaseFor($classroom, $settings),
            'semester_label' => $this->semesterLabel($semester),
            'academic_year' => $academicYear,
            'kepala_sekolah_name' => $settings->kepala_sekolah_name,
            'wali_kelas_name' => optional($classroom->waliKelas)->name,
            'parent_name' => $student->nama_wali
                ?: ($student->nama_ayah ?: $student->nama_ibu),
        ];

        // 2. Tabel nilai: hanya mapel yang KKM-nya diisi
        $subjects = Subject::query()
            ->printable()
            ->where('is_active', true)
            ->orderByRaw("FIELD(muatan, 'agama', 'nasional', 'lokal')")
            ->orderBy('name')
            ->get();

        $rows = $subjects->map(function (Subject $subject) use ($student, $academicYear, $semester) {
            $final = $this->scores->calculateFinal($student, $subject, $academicYear, $semester);

            return [
                'subject' => $subject,
                'final_score' => $final?->final_score,
                'kkm' => $subject->kkm,
                'capaian' => $this->capaianKompetensi($student, $subject, $academicYear, $semester),
            ];
        })->values();

        // 3. Ekstrakurikuler: HANYA muncul jika ada nilai yang diinput
        $extracurriculars = ExtracurricularScore::query()
            ->with('extracurricular')
            ->where('student_id', $student->id)
            ->where('academic_year', $academicYear)
            ->where('semester', $semester)
            ->whereNotNull('predikat')
            ->where('predikat', '<>', '')
            ->get();

        // 4. Kehadiran
        $attendance = Attendance::query()
            ->where('student_id', $student->id)
            ->where('academic_year', $academicYear)
            ->where('semester', $semester)
            ->first();

        return [
            'identity' => $identity,
            'rows' => $rows,
            'extracurriculars' => $extracurriculars,
            'has_extracurriculars' => $extracurriculars->isNotEmpty(),
            'attendance' => $attendance,
            'settings' => $settings,
            'printed_at' => now(),
        ];
    }

    /**
     * 2 deskripsi capaian otomatis: TP dengan nilai rata-rata tertinggi & terendah
     * yang dicapai siswa pada mapel tersebut.
     *
     * @return array<int, string>
     */
    public function capaianKompetensi(Student $student, Subject $subject, string $academicYear, string $semester): array
    {
        $tpScores = FormativeScore::query()
            ->select([
                'learning_objectives.id',
                'learning_objectives.code',
                'learning_objectives.description',
            ])
            ->selectRaw('AVG(formative_scores.score) as avg_score')
            ->join('learning_objectives', 'learning_objectives.id', '=', 'formative_scores.learning_objective_id')
            ->where('formative_scores.student_id', $student->id)
            ->where('formative_scores.subject_id', $subject->id)
            ->where('formative_scores.academic_year', $academicYear)
            ->where('formative_scores.semester', $semester)
            ->groupBy('learning_objectives.id', 'learning_objectives.code', 'learning_objectives.description')
            ->orderByDesc('avg_score')
            ->get();

        if ($tpScores->isEmpty()) {
            return ['-'];
        }

        $highest = $tpScores->first();
        $descriptions = [$highest->description];

        if ($tpScores->count() > 1) {
            $lowest = $tpScores->last();
            if ($lowest->id !== $highest->id) {
                $descriptions[] = $lowest->description;
            }
        }

        return array_values(array_unique($descriptions));
    }
}
