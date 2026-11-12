<?php

namespace Database\Seeders;

use App\Models\Subject;
use Illuminate\Database\Seeder;

class SubjectSeeder extends Seeder
{
    /**
     * 3 muatan mata pelajaran Kurikulum Merdeka SD.
     * KKM sengaja diisi (null = mapel tidak dicetak di rapor).
     */
    public function run(): void
    {
        $subjects = [
            // Muatan Agama
            ['code' => 'AG01', 'name' => "Al-Qur'an", 'muatan' => 'agama', 'kkm' => 75],
            ['code' => 'AG02', 'name' => 'Aqidah', 'muatan' => 'agama', 'kkm' => 75],
            ['code' => 'AG03', 'name' => 'Akhlak', 'muatan' => 'agama', 'kkm' => 75],
            ['code' => 'AG04', 'name' => 'Ibadah', 'muatan' => 'agama', 'kkm' => 75],
            ['code' => 'AG05', 'name' => 'Tarikh', 'muatan' => 'agama', 'kkm' => 75],
            ['code' => 'AG06', 'name' => 'Kemuhammadiyahan', 'muatan' => 'agama', 'kkm' => 75],
            ['code' => 'AG07', 'name' => 'Bahasa Arab', 'muatan' => 'agama', 'kkm' => 75],
            ['code' => 'AG08', 'name' => 'Tahsin Al-Qur\'an', 'muatan' => 'agama', 'kkm' => 75],
            ['code' => 'AG09', 'name' => 'Tahfidz Al-Qur\'an', 'muatan' => 'agama', 'kkm' => 75],

            // Muatan Nasional
            ['code' => 'NA01', 'name' => 'Pendidikan Pancasila', 'muatan' => 'nasional', 'kkm' => 75],
            ['code' => 'NA02', 'name' => 'Bahasa Indonesia', 'muatan' => 'nasional', 'kkm' => 75],
            ['code' => 'NA03', 'name' => 'Matematika', 'muatan' => 'nasional', 'kkm' => 75],
            ['code' => 'NA04', 'name' => 'Ilmu Pengetahuan Alam dan Sosial (IPAS)', 'muatan' => 'nasional', 'kkm' => 75],
            ['code' => 'NA05', 'name' => 'Seni Rupa', 'muatan' => 'nasional', 'kkm' => 70],
            ['code' => 'NA06', 'name' => 'Pendidikan Jasmani dan Olahraga (PJOK)', 'muatan' => 'nasional', 'kkm' => 70],

            // Muatan Lokal
            ['code' => 'LO01', 'name' => 'Bahasa Jawa', 'muatan' => 'lokal', 'kkm' => 75],
            ['code' => 'LO02', 'name' => 'Bahasa Inggris', 'muatan' => 'lokal', 'kkm' => 75],
            ['code' => 'LO03', 'name' => 'Batik', 'muatan' => 'lokal', 'kkm' => 70],
        ];

        foreach ($subjects as $subject) {
            Subject::query()->updateOrCreate(['code' => $subject['code']], $subject);
        }
    }
}
