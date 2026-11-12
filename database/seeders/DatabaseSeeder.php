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
    }
}
