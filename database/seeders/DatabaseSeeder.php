<?php

namespace Database\Seeders;

use App\Models\Classroom;
use App\Models\ReportSetting;
use App\Models\Subject;
use App\Models\User;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        // Pengaturan rapor singleton
        ReportSetting::current();

        // 18 mata pelajaran (3 muatan)
        $this->call(SubjectSeeder::class);

        // Akun default (ganti password setelah login pertama!)
        User::query()->updateOrCreate(['email' => 'admin@sekolah.test'], [
            'name' => 'Super Administrator',
            'password' => 'password',
            'role' => User::ROLE_SUPER_ADMIN,
        ]);

        User::query()->updateOrCreate(['email' => 'kepsek@sekolah.test'], [
            'name' => 'Kepala Sekolah',
            'password' => 'password',
            'role' => User::ROLE_KEPALA_SEKOLAH,
        ]);

        $wali = User::query()->updateOrCreate(['email' => 'wali@sekolah.test'], [
            'name' => 'Wali Kelas 1A',
            'password' => 'password',
            'role' => User::ROLE_WALI_KELAS,
        ]);

        // Rombel contoh kelas 1-6
        $year = date('Y').'/'.(date('Y') + 1);

        foreach (range(1, 6) as $grade) {
            Classroom::query()->updateOrCreate(
                ['name' => (string) $grade, 'grade' => $grade, 'academic_year' => $year],
                ['wali_kelas_id' => $grade === 1 ? $wali->id : null]
            );
        }

        User::query()->updateOrCreate(['email' => 'guru@sekolah.test'], [
            'name' => 'Guru Kelas 1',
            'password' => 'password',
            'role' => User::ROLE_GURU,
            'classroom_id' => Classroom::query()->where('grade', 1)->where('academic_year', $year)->value('id'),
        ]);

        // Akun demo Siswa/Orang Tua — tertaut ke data siswa (bila ada)
        $demoStudent = \App\Models\Student::query()
            ->where('nisn', '0012345678')
            ->first();

        if (! $demoStudent) {
            $kelas1 = Classroom::query()->where('grade', 1)->where('academic_year', $year)->first();

            $demoStudent = $kelas1 ? \App\Models\Student::query()->create([
                'nisn' => '0012345678',
                'nis' => 'SD-001',
                'nama' => 'Ahmad Fauzi',
                'jenis_kelamin' => 'Laki-laki',
                'tempat_lahir' => 'Padang',
                'tanggal_lahir' => date('Y-07-10', strtotime('-6 years')),
                'alamat' => 'Jl. Pendidikan No. 1, Padang',
                'nama_ayah' => 'Budi Santoso',
                'nama_ibu' => 'Siti Aminah',
                'nama_wali' => 'Budi Santoso',
                'classroom_id' => $kelas1->id,
                'is_active' => true,
            ]) : null;
        }

        User::query()->updateOrCreate(['email' => 'siswa@sekolah.test'], [
            'name' => $demoStudent?->nama ?? 'Siswa Demo',
            'password' => 'password',
            'role' => User::ROLE_SISWA_ORANGTUA,
            'student_id' => $demoStudent?->id,
        ]);
    }
}
