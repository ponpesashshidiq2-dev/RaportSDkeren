<?php

namespace App\Imports;

use App\Models\Classroom;
use App\Models\Student;
use Illuminate\Support\Facades\Validator;
use Maatwebsite\Excel\Concerns\Importable;
use Maatwebsite\Excel\Concerns\SkipsEmptyRows;
use Maatwebsite\Excel\Concerns\ToModel;
use Maatwebsite\Excel\Concerns\WithHeadingRow;
use Maatwebsite\Excel\Concerns\WithValidation;

/**
 * Import data siswa via Excel (.xlsx/.xls/.csv).
 *
 * Header baris pertama (wajib):
 *   NISN | NIS | Nama | Jenis Kelamin | Tempat Lahir | Tanggal Lahir |
 *   Alamat | No HP | Nama Ayah | Nama Ibu | Kelas
 *
 * Kolom "Kelas" dipetakan ke classrooms.name (contoh: 1A).
 * Jika kolom Kelas kosong, memakai kelas default dari form upload.
 */
class StudentsImport implements ToModel, WithHeadingRow, WithValidation, SkipsEmptyRows
{
    use Importable;

    public static int $importedCount = 0;

    public function __construct(
        private ?int $defaultClassroomId = null,
        private ?int $createdBy = null,
    ) {}

    public function model(array $row): Student
    {
        self::$importedCount++;

        $classroomId = $this->resolveClassroomId($row);

        return new Student([
            'nisn' => $this->nullableString($row['nisn'] ?? null),
            'nis' => $this->nullableString($row['nis'] ?? null),
            'nama' => trim((string) ($row['nama'] ?? '')),
            'jenis_kelamin' => $this->normalizeGender($row['jenis_kelamin'] ?? ''),
            'tempat_lahir' => trim((string) ($row['tempat_lahir'] ?? '')),
            'tanggal_lahir' => $this->parseDate($row['tanggal_lahir'] ?? null),
            'alamat' => trim((string) ($row['alamat'] ?? '')),
            'no_hp' => $this->nullableString($row['no_hp'] ?? null),
            'nama_ayah' => $this->nullableString($row['nama_ayah'] ?? null),
            'nama_ibu' => $this->nullableString($row['nama_ibu'] ?? null),
            'nama_wali' => $this->nullableString($row['nama_wali'] ?? null),
            'classroom_id' => $classroomId,
            'created_by' => $this->createdBy,
            'is_active' => true,
        ]);
    }

    /**
     * @return array<string, string>
     */
    public function rules(): array
    {
        return [
            'nama' => 'required|string|max:255',
            'nisn' => 'nullable|string|max:20',
            'jenis_kelamin' => 'required|string|max:20',
            'tempat_lahir' => 'required|string|max:100',
            'tanggal_lahir' => 'required',
            'alamat' => 'required|string',
        ];
    }

    /**
     * @return array<string, string>
     */
    public function customValidationMessages(): array
    {
        return [
            'nama.required' => 'Kolom Nama wajib diisi.',
            'jenis_kelamin.required' => 'Kolom Jenis Kelamin wajib diisi.',
            'tempat_lahir.required' => 'Kolom Tempat Lahir wajib diisi.',
            'tanggal_lahir.required' => 'Kolom Tanggal Lahir wajib diisi.',
            'alamat.required' => 'Kolom Alamat wajib diisi.',
        ];
    }

    private function resolveClassroomId(array $row): ?int
    {
        $kelas = trim((string) ($row['kelas'] ?? ''));

        if ($kelas !== '') {
            $classroom = Classroom::query()->where('name', $kelas)->first();

            if (! $classroom) {
                Validator::make(
                    ['kelas' => $kelas],
                    ['kelas' => 'required|exists:classrooms,name'],
                    ['kelas.exists' => "Kelas '{$kelas}' tidak ditemukan. Buat dulu di menu Data Kelas."]
                )->validate();
            }

            return $classroom->id;
        }

        return $this->defaultClassroomId;
    }

    private function normalizeGender(mixed $value): string
    {
        $value = strtolower(trim((string) $value));

        if (str_starts_with($value, 'l') || str_contains($value, 'laki')) {
            return 'L';
        }

        return 'P';
    }

    private function parseDate(mixed $value): ?string
    {
        if ($value === null || $value === '') {
            return null;
        }

        if ($value instanceof \DateTimeInterface) {
            return $value->format('Y-m-d');
        }

        foreach (['Y-m-d', 'd/m/Y', 'd-m-Y', 'm/d/Y', 'd M Y'] as $format) {
            $date = \DateTime::createFromFormat($format, trim((string) $value));
            if ($date instanceof \DateTime) {
                return $date->format('Y-m-d');
            }
        }

        try {
            return (new \DateTime(trim((string) $value)))->format('Y-m-d');
        } catch (\Throwable) {
            return null;
        }
    }

    private function nullableString(mixed $value): ?string
    {
        $value = trim((string) ($value ?? ''));

        return $value === '' ? null : $value;
    }
}
