<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ReportSetting extends Model
{
    protected $fillable = [
        'school_name',
        'npsn',
        'address',
        'city',
        'phone',
        'email',
        'kepala_sekolah_name',
        'kepala_sekolah_nip',
        'active_academic_year',
        'active_semester',
        'formatif_weight',
        'sumatif_weight',
        'default_phase',
        'report_footer',
    ];

    protected $casts = [
        'formatif_weight' => 'float',
        'sumatif_weight' => 'float',
    ];

    /** Ambil pengaturan singleton (baris pertama), buat jika belum ada. */
    public static function current(): self
    {
        return self::query()->firstOrCreate(['id' => 1], [
            'school_name' => 'SD Kurikulum Merdeka',
            'active_academic_year' => date('Y').'/'.(date('Y') + 1),
            'active_semester' => '1',
            'formatif_weight' => 40,
            'sumatif_weight' => 60,
            'default_phase' => 'A',
        ]);
    }

    public function semesterLabel(): string
    {
        return $this->active_semester === '1' ? 'Ganjil' : 'Genap';
    }
}
