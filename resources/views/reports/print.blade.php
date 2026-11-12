<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Rapor {{ $identity['student']->nama }} - {{ $identity['academic_year'] }}</title>
    <style>
        @page { size: A4 portrait; margin: 15mm; }
        * { margin: 0; padding: 0; box-sizing: border-box; }
        body {
            font-family: "Times New Roman", Times, serif;
            font-size: 11pt;
            color: #111;
        }
        .header { text-align: center; border-bottom: 2px solid #111; padding-bottom: 6px; margin-bottom: 12px; }
        .header h1 { font-size: 15pt; text-transform: uppercase; letter-spacing: 1px; }
        .header p { font-size: 9.5pt; }
        .header .subtitle { font-size: 11pt; font-weight: bold; text-transform: uppercase; letter-spacing: 3px; margin-top: 4px; }
        .header .period { font-size: 9.5pt; }

        table { width: 100%; border-collapse: collapse; }
        td, th { border: 1px solid #333; padding: 4px 6px; vertical-align: top; }
        .identity td { border: none; border-bottom: 1px solid #999; padding: 3px 4px; }
        .identity td.label { width: 150px; background: #f5f5f5; }
        .identity { margin-bottom: 14px; border-top: 1px solid #333; }

        .section-title { font-weight: bold; font-size: 11pt; margin: 12px 0 4px 0; }

        thead th { background: #eee; text-align: center; font-size: 10pt; }
        .scores td { font-size: 10pt; }
        .scores .center { text-align: center; }
        .scores .score { text-align: center; font-weight: bold; width: 70px; }
        .scores .no { text-align: center; width: 30px; }
        .scores .subject { width: 150px; }
        .capaian { font-size: 9pt; line-height: 1.35; }
        .capaian ol { margin-left: 14px; }

        .attendance { width: 70%; }
        .attendance th, .attendance td { text-align: center; }

        .signature { margin-top: 28px; font-size: 10.5pt; }
        .signature table td { border: none; text-align: center; height: 60px; vertical-align: bottom; }
        .signature table td.title { height: auto; font-weight: normal; vertical-align: top; }
        .signature .place-date { margin-bottom: 4px; }

        .page-break { page-break-before: always; }
    </style>
</head>
<body>

    {{-- ================= LEMBAR PERTAMA: IDENTITAS ================= --}}
    <div class="header">
        <h1>{{ $identity['school_name'] }}</h1>
        <p>
            @if($identity['npsn']) NPSN: {{ $identity['npsn'] }} &nbsp;|&nbsp; @endif
            {{ $identity['address'] }}
        </p>
        <div class="subtitle">Rapor Peserta Didik</div>
        <div class="period">{{ $identity['semester_label'] }} &mdash; Tahun Ajaran {{ $identity['academic_year'] }}</div>
    </div>

    <table class="identity">
        <tr>
            <td class="label">Nama Peserta Didik</td>
            <td>: {{ $identity['student']->nama }}</td>
        </tr>
        <tr>
            <td class="label">NISN</td>
            <td>: {{ $identity['student']->nisn ?? '-' }}</td>
        </tr>
        <tr>
            <td class="label">Sekolah</td>
            <td>: {{ $identity['school_name'] }}</td>
        </tr>
        <tr>
            <td class="label">Alamat</td>
            <td>: {{ $identity['student']->alamat }}</td>
        </tr>
        <tr>
            <td class="label">Kelas</td>
            <td>: {{ $identity['classroom']->name }}</td>
        </tr>
        <tr>
            <td class="label">Fase</td>
            <td>: {{ $identity['phase'] }}</td>
        </tr>
        <tr>
            <td class="label">Semester</td>
            <td>: {{ $identity['semester_label'] }}</td>
        </tr>
        <tr>
            <td class="label">Tahun Ajaran</td>
            <td>: {{ $identity['academic_year'] }}</td>
        </tr>
    </table>

    {{-- ================= TABEL NILAI ================= --}}
    <table class="scores">
        <thead>
            <tr>
                <th class="no">No</th>
                <th class="subject">Mata Pelajaran</th>
                <th class="score">Nilai Akhir</th>
                <th>Capaian Kompetensi</th>
            </tr>
        </thead>
        <tbody>
            @forelse($rows as $index => $row)
                <tr>
                    <td class="no center">{{ $index + 1 }}</td>
                    <td>{{ $row['subject']->name }}</td>
                    <td class="score">
                        {{ $row['final_score'] !== null ? rtrim(rtrim(number_format($row['final_score'], 1, '.', ''), '0'), '.') : '-' }}
                    </td>
                    <td class="capaian">
                        <ol>
                            @foreach($row['capaian'] as $desc)
                                <li>{{ $desc }}</li>
                            @endforeach
                        </ol>
                    </td>
                </tr>
            @empty
                <tr>
                    <td colspan="4" class="center">Tidak ada mata pelajaran ber-KKM.</td>
                </tr>
            @endforelse
        </tbody>
    </table>

    {{-- ================= EKSTRAKURIKULER (dinamis) ================= --}}
    @if($has_extracurriculars)
        <div class="section-title">A. Ekstrakurikuler</div>
        <table>
            <thead>
                <tr>
                    <th style="width:30px">No</th>
                    <th>Kegiatan</th>
                    <th style="width:140px">Predikat</th>
                </tr>
            </thead>
            <tbody>
                @foreach($extracurriculars as $index => $ex)
                    <tr>
                        <td class="center">{{ $index + 1 }}</td>
                        <td>{{ $ex->extracurricular?->name }}</td>
                        <td class="center">{{ $ex->predikat }}</td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    @endif

    {{-- ================= KEHADIRAN ================= --}}
    <div class="section-title">{{ $has_extracurriculars ? 'B' : 'A' }}. Kehadiran</div>
    <table class="attendance">
        <thead>
            <tr>
                <th>Sakit</th>
                <th>Izin</th>
                <th>Tanpa Keterangan</th>
            </tr>
        </thead>
        <tbody>
            <tr>
                <td>{{ $attendance?->sakit ?? 0 }} hari</td>
                <td>{{ $attendance?->izin ?? 0 }} hari</td>
                <td>{{ $attendance?->tanpa_keterangan ?? 0 }} hari</td>
            </tr>
        </tbody>
    </table>

    {{-- ================= TANDA TANGAN ================= --}}
    <div class="signature">
        <div class="place-date">
            {{ $identity['city'] ?? '........................' }},
            {{ $printed_at->translatedFormat('d F Y') }}
        </div>
        <table>
            <tr>
                <td class="title">Wali Kelas</td>
                <td class="title">Orang Tua/Wali Murid</td>
                <td class="title">Kepala Sekolah</td>
            </tr>
            <tr>
                <td>( {{ $identity['wali_kelas_name'] ?? '........................' }} )</td>
                <td>( {{ $identity['parent_name'] ?? '........................' }} )</td>
                <td>( {{ $identity['kepala_sekolah_name'] ?? '........................' }} )</td>
            </tr>
        </table>
    </div>

</body>
</html>
