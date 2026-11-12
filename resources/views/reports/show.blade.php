@extends('layouts.app')

@section('title', 'Preview Rapor - '.$identity['student']->nama)

@section('content')
    <div class="flex flex-wrap items-center justify-between gap-3 mb-5">
        <a href="{{ route('reports.index', request()->query('student_id') ? ['classroom_id' => $identity['classroom']->id] + request()->query() : request()->query()) }}"
           class="rounded-lg bg-slate-200 hover:bg-slate-300 px-4 py-2 text-sm font-medium">
            &larr; Kembali
        </a>
        <a href="{{ route('reports.print', request()->query()) }}"
           class="rounded-lg bg-rose-600 hover:bg-rose-700 text-white px-4 py-2 text-sm font-medium">
            Cetak / Unduh PDF (A4)
        </a>
    </div>

    {{-- Lembar Rapor (preview layar) --}}
    <div class="mx-auto bg-white shadow-xl border border-slate-200" style="width: 210mm; min-height: 297mm; padding: 18mm;">
        {{-- Identitas (lembar pertama) --}}
        <div class="text-center border-b-2 border-slate-800 pb-3 mb-4">
            <div class="text-lg font-bold uppercase tracking-wide">{{ $identity['school_name'] }}</div>
            <div class="text-xs text-slate-600">
                @if($identity['npsn']) NPSN: {{ $identity['npsn'] }} &middot; @endif
                {{ $identity['address'] }}
            </div>
            <div class="text-sm font-semibold uppercase mt-2 tracking-widest">Rapor Peserta Didik</div>
            <div class="text-xs text-slate-500">
                {{ $identity['semester_label'] }} &middot; Tahun Ajaran {{ $identity['academic_year'] }}
            </div>
        </div>

        <table class="w-full text-sm mb-6 border border-slate-300">
            <tbody>
                <tr class="border-b border-slate-300">
                    <td class="px-3 py-2 w-56 bg-slate-50">Nama Peserta Didik</td>
                    <td class="px-3 py-2 font-semibold">: {{ $identity['student']->nama }}</td>
                </tr>
                <tr class="border-b border-slate-300">
                    <td class="px-3 py-2 bg-slate-50">NISN</td>
                    <td class="px-3 py-2">: {{ $identity['student']->nisn ?? '-' }}</td>
                </tr>
                <tr class="border-b border-slate-300">
                    <td class="px-3 py-2 bg-slate-50">Sekolah</td>
                    <td class="px-3 py-2">: {{ $identity['school_name'] }}</td>
                </tr>
                <tr class="border-b border-slate-300">
                    <td class="px-3 py-2 bg-slate-50">Alamat</td>
                    <td class="px-3 py-2">: {{ $identity['student']->alamat }}</td>
                </tr>
                <tr class="border-b border-slate-300">
                    <td class="px-3 py-2 bg-slate-50">Kelas</td>
                    <td class="px-3 py-2">: {{ $identity['classroom']->name }}</td>
                </tr>
                <tr class="border-b border-slate-300">
                    <td class="px-3 py-2 bg-slate-50">Fase</td>
                    <td class="px-3 py-2">: {{ $identity['phase'] }}</td>
                </tr>
                <tr class="border-b border-slate-300">
                    <td class="px-3 py-2 bg-slate-50">Semester</td>
                    <td class="px-3 py-2">: {{ $identity['semester_label'] }}</td>
                </tr>
                <tr>
                    <td class="px-3 py-2 bg-slate-50">Tahun Ajaran</td>
                    <td class="px-3 py-2">: {{ $identity['academic_year'] }}</td>
                </tr>
            </tbody>
        </table>

        {{-- Tabel Nilai --}}
        <table class="w-full text-sm mb-6 border border-slate-300">
            <thead class="bg-slate-100">
                <tr class="border-b border-slate-300">
                    <th class="border-r border-slate-300 px-2 py-2 w-10">No</th>
                    <th class="border-r border-slate-300 px-2 py-2 text-left">Mata Pelajaran</th>
                    <th class="border-r border-slate-300 px-2 py-2 w-24">Nilai Akhir</th>
                    <th class="px-2 py-2 text-left">Capaian Kompetensi</th>
                </tr>
            </thead>
            <tbody>
                @forelse($rows as $index => $row)
                    <tr class="border-b border-slate-200 align-top">
                        <td class="border-r border-slate-300 px-2 py-2 text-center">{{ $index + 1 }}</td>
                        <td class="border-r border-slate-300 px-2 py-2">{{ $row['subject']->name }}</td>
                        <td class="border-r border-slate-300 px-2 py-2 text-center font-bold">
                            {{ $row['final_score'] !== null ? rtrim(rtrim(number_format($row['final_score'], 1, '.', ''), '0'), '.') : '-' }}
                        </td>
                        <td class="px-2 py-2 text-xs leading-relaxed">
                            <ol class="list-decimal list-inside space-y-1">
                                @foreach($row['capaian'] as $desc)
                                    <li>{{ $desc }}</li>
                                @endforeach
                            </ol>
                        </td>
                    </tr>
                @empty
                    <tr><td colspan="4" class="px-3 py-4 text-center text-slate-400 text-sm">
                        Tidak ada mata pelajaran ber-KKM.
                    </td></tr>
                @endforelse
            </tbody>
        </table>

        {{-- Ekstrakurikuler: hanya muncul jika ada data --}}
        @if($has_extracurriculars)
            <div class="text-sm font-semibold mb-1">A. Ekstrakurikuler</div>
            <table class="w-full text-sm mb-6 border border-slate-300">
                <thead class="bg-slate-100">
                    <tr class="border-b border-slate-300">
                        <th class="border-r border-slate-300 px-2 py-2 w-10">No</th>
                        <th class="border-r border-slate-300 px-2 py-2 text-left">Kegiatan</th>
                        <th class="px-2 py-2 text-left">Predikat</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($extracurriculars as $index => $ex)
                        <tr class="border-b border-slate-200">
                            <td class="border-r border-slate-300 px-2 py-2 text-center">{{ $index + 1 }}</td>
                            <td class="border-r border-slate-300 px-2 py-2">{{ $ex->extracurricular?->name }}</td>
                            <td class="px-2 py-2">{{ $ex->predikat }}</td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        @endif

        {{-- Kehadiran --}}
        <div class="text-sm font-semibold mb-1">{{ $has_extracurriculars ? 'B' : 'A' }}. Kehadiran</div>
        <table class="w-full text-sm mb-8 border border-slate-300">
            <thead class="bg-slate-100">
                <tr class="border-b border-slate-300">
                    <th class="border-r border-slate-300 px-2 py-2 w-16">Sakit</th>
                    <th class="border-r border-slate-300 px-2 py-2 w-16">Izin</th>
                    <th class="px-2 py-2">Tanpa Keterangan</th>
                </tr>
            </thead>
            <tbody>
                <tr>
                    <td class="border-r border-slate-300 px-2 py-2 text-center font-semibold">
                        {{ $attendance?->sakit ?? 0 }} hari
                    </td>
                    <td class="border-r border-slate-300 px-2 py-2 text-center font-semibold">
                        {{ $attendance?->izin ?? 0 }} hari
                    </td>
                    <td class="px-2 py-2 text-center font-semibold">
                        {{ $attendance?->tanpa_keterangan ?? 0 }} hari
                    </td>
                </tr>
            </tbody>
        </table>

        {{-- Tanda tangan --}}
        <div class="text-sm">
            <div class="mb-4">
                {{ $identity['city'] ?? '........................' }},
                {{ $printed_at->translatedFormat('d F Y') }}
            </div>

            <table class="w-full">
                <thead>
                    <tr>
                        <th class="w-1/3 font-normal">Wali Kelas</th>
                        <th class="w-1/3 font-normal">Orang Tua/Wali Murid</th>
                        <th class="w-1/3 font-normal">Kepala Sekolah</th>
                    </tr>
                </thead>
                <tbody>
                    <tr>
                        <td class="h-20 align-bottom text-center">(&nbsp;{{ $identity['wali_kelas_name'] ?? '........................' }}&nbsp;)</td>
                        <td class="h-20 align-bottom text-center">(&nbsp;{{ $identity['parent_name'] ?? '........................' }}&nbsp;)</td>
                        <td class="h-20 align-bottom text-center">(&nbsp;{{ $identity['kepala_sekolah_name'] ?? '........................' }}&nbsp;)</td>
                    </tr>
                    <tr>
                        <td class="text-center text-xs">{{ $identity['wali_kelas_name'] ? 'Wali Kelas' : '' }}</td>
                        <td class="text-center text-xs">{{ $identity['parent_name'] ? 'Orang Tua/Wali' : '' }}</td>
                        <td class="text-center text-xs">{{ $identity['kepala_sekolah_name'] ? 'Kepala Sekolah' : '' }}</td>
                    </tr>
                </tbody>
            </table>
        </div>
    </div>
@endsection
