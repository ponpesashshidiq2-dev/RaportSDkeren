@extends('layouts.app')

@section('title', 'Nilai Saya')

@section('content')
    @if($error)
        <div class="bg-white rounded-xl shadow-sm border border-slate-200 p-8 text-center">
            <div class="text-4xl mb-3">🔒</div>
            <p class="text-slate-600">{{ $error }}</p>
        </div>
    @else

    {{-- Identitas siswa + filter periode --}}
    <div class="bg-white rounded-xl shadow-sm border border-slate-200 p-5 mb-5 flex flex-wrap items-end justify-between gap-4">
        <div>
            <div class="text-sm text-slate-500">Peserta Didik</div>
            <div class="text-lg font-semibold text-slate-800">{{ $student->nama }}</div>
            <div class="text-xs text-slate-500">
                NISN: {{ $student->nisn ?? '-' }} • Kelas {{ $student->classroom?->name ?? '-' }}
            </div>
        </div>

        <form method="GET" class="flex flex-wrap items-end gap-2">
            <div>
                <label class="block text-xs text-slate-500 mb-1">Tahun Ajaran</label>
                <input type="text" name="tahun_ajaran" value="{{ $year }}" readonly
                       class="rounded-lg border border-slate-300 bg-slate-50 px-3 py-2 text-sm w-32 text-slate-600">
            </div>
            <div>
                <label class="block text-xs text-slate-500 mb-1">Semester</label>
                <select name="semester" class="rounded-lg border border-slate-300 px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-indigo-500">
                    <option value="1" {{ $semester == '1' ? 'selected' : '' }}>Semester 1 (Ganjil)</option>
                    <option value="2" {{ $semester == '2' ? 'selected' : '' }}>Semester 2 (Genap)</option>
                </select>
            </div>
            <button class="rounded-lg bg-slate-200 hover:bg-slate-300 px-4 py-2 text-sm font-medium">Tampilkan</button>

            <a href="{{ route('reports.show') }}"
               class="rounded-lg border border-indigo-300 text-indigo-700 hover:bg-indigo-50 px-4 py-2 text-sm font-medium">Lihat Rapor</a>
            <a href="{{ route('reports.print') }}"
               class="rounded-lg bg-indigo-600 hover:bg-indigo-700 text-white px-4 py-2 text-sm font-medium">Cetak PDF</a>
        </form>
    </div>

    {{-- Tabel nilai akhir per mapel --}}
    <div class="bg-white rounded-xl shadow-sm border border-slate-200 overflow-x-auto mb-5">
        <table class="w-full text-sm">
            <thead class="bg-slate-50 text-slate-500 text-xs uppercase">
                <tr>
                    <th class="text-left px-4 py-3 w-10">No</th>
                    <th class="text-left px-4 py-3">Mata Pelajaran</th>
                    <th class="text-center px-4 py-3">KKM</th>
                    <th class="text-center px-4 py-3">Nilai Akhir</th>
                    <th class="text-center px-4 py-3">Semester Lalu</th>
                    <th class="text-center px-4 py-3">Status</th>
                    <th class="text-left px-4 py-3">Detail</th>
                </tr>
            </thead>
            <tbody>
                @forelse($rows as $i => $row)
                    @php
                        $isDown = $row->decreased;
                        $finalCell = $row->final;
                    @endphp
                    <tr class="border-t border-slate-100 {{ $isDown ? 'bg-rose-50/60' : 'hover:bg-slate-50' }}">
                        <td class="px-4 py-3 text-slate-400">{{ $i + 1 }}</td>
                        <td class="px-4 py-3 font-medium text-slate-800">{{ $row->subject->name }}</td>
                        <td class="px-4 py-3 text-center text-slate-500">{{ $row->subject->kkm ?? '-' }}</td>
                        <td class="px-4 py-3 text-center font-bold {{ $isDown ? 'text-rose-600' : 'text-slate-800' }}">
                            @if($finalCell !== null)
                                {{ rtrim(rtrim(number_format($finalCell, 1), '0'), '.') }}
                                @if($isDown) <span class="align-middle">&#9660;</span> @endif
                            @else
                                <span class="text-slate-300 font-normal">—</span>
                            @endif
                        </td>
                        <td class="px-4 py-3 text-center text-slate-500">
                            {{ $row->previous !== null ? rtrim(rtrim(number_format($row->previous, 1), '0'), '.') : '—' }}
                        </td>
                        <td class="px-4 py-3 text-center">
                            @if($row->final === null)
                                <span class="text-[11px] uppercase rounded px-2 py-0.5 bg-slate-100 text-slate-400">Belum ada nilai</span>
                            @elseif($isDown)
                                <span class="text-[11px] uppercase rounded px-2 py-0.5 bg-rose-100 text-rose-700">Turun</span>
                            @elseif($row->tuntas)
                                <span class="text-[11px] uppercase rounded px-2 py-0.5 bg-emerald-100 text-emerald-700">Tuntas</span>
                            @else
                                <span class="text-[11px] uppercase rounded px-2 py-0.5 bg-amber-100 text-amber-700">Belum tuntas</span>
                            @endif
                        </td>
                        <td class="px-4 py-3">
                            @if($row->final !== null)
                                <a href="{{ route('my-scores.index', array_filter([
                                    'subject_id' => $row->subject->id,
                                    'tahun_ajaran' => $year,
                                    'semester' => $semester,
                                ])) }}"
                                   class="{{ $detailSubject?->id === $row->subject->id ? 'text-indigo-800 font-semibold' : 'text-indigo-600' }} hover:underline text-xs font-semibold">
                                    {{ $detailSubject?->id === $row->subject->id ? 'Tutup' : 'Rincian' }}
                                </a>
                            @endif
                        </td>
                    </tr>
                @empty
                    <tr><td colspan="7" class="px-4 py-8 text-center text-slate-400">Belum ada mata pelajaran dengan KKM.</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>

    {{-- Rincian satu mapel --}}
    @if($detailSubject)
        <div class="bg-white rounded-xl shadow-sm border border-indigo-200 p-5 mb-5">
            <div class="flex flex-wrap items-center justify-between gap-2 mb-4">
                <h2 class="font-semibold text-slate-700">
                    Rincian Nilai — {{ $detailSubject->name }}
                </h2>
                <a href="{{ route('my-scores.index', array_filter(['tahun_ajaran' => $year, 'semester' => $semester])) }}"
                   class="text-xs text-slate-500 hover:text-slate-700 hover:underline">Tutup rincian ✕</a>
            </div>

            <div class="grid lg:grid-cols-2 gap-5">
                <div class="overflow-x-auto">
                    <div class="text-xs uppercase text-slate-500 mb-2">Formatif (per Tujuan Pembelajaran)</div>
                    @if($formatifDetail->isEmpty())
                        <p class="text-sm text-slate-400">Belum ada nilai formatif.</p>
                    @else
                        <table class="w-full text-sm">
                            <thead class="bg-slate-50 text-slate-500 text-xs uppercase">
                                <tr>
                                    <th class="text-left px-3 py-2">TP</th>
                                    <th class="text-left px-3 py-2">Deskripsi</th>
                                    <th class="text-center px-3 py-2">Nilai</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($formatifDetail as $f)
                                    <tr class="border-t border-slate-100">
                                        <td class="px-3 py-2 font-mono text-xs">{{ $f->learningObjective?->code ?? '-' }}</td>
                                        <td class="px-3 py-2 text-xs text-slate-600">{{ $f->learningObjective?->description ?? '-' }}</td>
                                        <td class="px-3 py-2 text-center font-semibold">{{ rtrim(rtrim(number_format((float) $f->score, 1), '0'), '.') }}</td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    @endif
                </div>

                <div class="overflow-x-auto">
                    <div class="text-xs uppercase text-slate-500 mb-2">Sumatif (Lingkup Materi)</div>
                    @if($sumatifDetail->isEmpty())
                        <p class="text-sm text-slate-400">Belum ada nilai sumatif.</p>
                    @else
                        <table class="w-full text-sm">
                            <thead class="bg-slate-50 text-slate-500 text-xs uppercase">
                                <tr>
                                    <th class="text-left px-3 py-2">Lingkup Materi</th>
                                    <th class="text-left px-3 py-2">Tanggal</th>
                                    <th class="text-center px-3 py-2">Bobot</th>
                                    <th class="text-center px-3 py-2">Nilai</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($sumatifDetail as $s)
                                    <tr class="border-t border-slate-100">
                                        <td class="px-3 py-2 text-xs text-slate-600">{{ $s->scope }}</td>
                                        <td class="px-3 py-2 text-xs text-slate-500">{{ optional($s->assessed_at)->translatedFormat('d M Y') }}</td>
                                        <td class="px-3 py-2 text-center text-xs">{{ $s->weight }}%</td>
                                        <td class="px-3 py-2 text-center font-semibold">{{ rtrim(rtrim(number_format((float) $s->score, 1), '0'), '.') }}</td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                        @php
                            $row = collect($rows)->firstWhere('subject.id', $detailSubject->id);
                        @endphp
                        @if($row && $row->avg_formatif !== null && $row->avg_sumatif !== null)
                            <div class="mt-3 text-xs text-slate-500 flex flex-wrap gap-4">
                                <span>Rata-rata formatif: <strong class="text-slate-700">{{ rtrim(rtrim(number_format((float) $row->avg_formatif, 1), '0'), '.') }}</strong></span>
                                <span>Rata-rata sumatif: <strong class="text-slate-700">{{ rtrim(rtrim(number_format((float) $row->avg_sumatif, 1), '0'), '.') }}</strong></span>
                                <span>Bobot 40% formatif + 60% sumatif →
                                    <strong class="{{ $row->decreased ? 'text-rose-600' : 'text-slate-700' }}">
                                        {{ $row->final !== null ? rtrim(rtrim(number_format((float) $row->final, 1), '0'), '.') : '-' }}
                                    </strong>
                                </span>
                            </div>
                        @endif
                    @endif
                </div>
            </div>
        </div>
    @endif

    {{-- Kehadiran + Ekstrakurikuler --}}
    <div class="grid lg:grid-cols-2 gap-5">
        <div class="bg-white rounded-xl shadow-sm border border-slate-200 p-5">
            <h2 class="font-semibold text-slate-700 mb-3">Kehadiran</h2>
            @if($attendance)
                <div class="grid grid-cols-3 gap-3 text-center">
                    <div class="rounded-lg bg-emerald-50 border border-emerald-100 py-3">
                        <div class="text-2xl font-bold text-emerald-700">{{ $attendance->hadir }}</div>
                        <div class="text-[11px] uppercase text-emerald-600">Hadir</div>
                    </div>
                    <div class="rounded-lg bg-amber-50 border border-amber-100 py-3">
                        <div class="text-2xl font-bold text-amber-700">{{ $attendance->sakit }}</div>
                        <div class="text-[11px] uppercase text-amber-600">Sakit</div>
                    </div>
                    <div class="rounded-lg bg-sky-50 border border-sky-100 py-3">
                        <div class="text-2xl font-bold text-sky-700">{{ $attendance->izin }}</div>
                        <div class="text-[11px] uppercase text-sky-600">Izin</div>
                    </div>
                </div>
                @if($attendance->alpa > 0)
                    <div class="mt-3 rounded-lg bg-rose-50 border border-rose-100 px-4 py-2 text-sm text-rose-700 text-center">
                        Alpa: {{ $attendance->alpa }} hari
                    </div>
                @endif
                @if($attendance->notes)
                    <p class="mt-3 text-xs text-slate-500">Catatan: {{ $attendance->notes }}</p>
                @endif
            @else
                <p class="text-sm text-slate-400">Belum ada data kehadiran semester ini.</p>
            @endif
        </div>

        <div class="bg-white rounded-xl shadow-sm border border-slate-200 p-5">
            <h2 class="font-semibold text-slate-700 mb-3">Ekstrakurikuler</h2>
            @if($extracurriculars->isEmpty())
                <p class="text-sm text-slate-400">Belum ada nilai ekstrakurikuler.</p>
            @else
                <ul class="space-y-2">
                    @foreach($extracurriculars as $ex)
                        <li class="flex items-center justify-between border-b border-slate-100 pb-2 text-sm">
                            <span class="font-medium text-slate-700">{{ $ex->extracurricular?->name ?? '-' }}</span>
                            <span class="text-xs uppercase rounded px-2 py-0.5 bg-indigo-50 text-indigo-700 font-semibold">{{ $ex->predikat }}</span>
                        </li>
                    @endforeach
                </ul>
            @endif
        </div>
    </div>
    @endif
@endsection
