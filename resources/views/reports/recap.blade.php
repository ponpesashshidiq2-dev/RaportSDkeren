@extends('layouts.app')

@section('title', 'Rekap Nilai per Mapel')

@section('content')
    <form method="GET" class="bg-white rounded-xl shadow-sm border border-slate-200 p-4 mb-5 flex flex-wrap gap-3 items-end">
        <div>
            <label class="block text-xs text-slate-500 mb-1">Kelas</label>
            <select name="classroom_id" required class="rounded-lg border border-slate-300 px-3 py-2 text-sm">
                <option value="">-- Pilih --</option>
                @foreach($classrooms as $rombel)
                    <option value="{{ $rombel->id }}" {{ $classroom?->id == $rombel->id ? 'selected' : '' }}>{{ $rombel->name }}</option>
                @endforeach
            </select>
        </div>
        <div>
            <label class="block text-xs text-slate-500 mb-1">Mata Pelajaran (opsional)</label>
            <select name="subject_id" class="rounded-lg border border-slate-300 px-3 py-2 text-sm max-w-xs">
                <option value="">Semua mapel</option>
                @foreach($subjects as $sub)
                    <option value="{{ $sub->id }}" {{ $subject?->id == $sub->id ? 'selected' : '' }}>{{ $sub->name }}</option>
                @endforeach
            </select>
        </div>
        <div>
            <label class="block text-xs text-slate-500 mb-1">Tahun Ajaran</label>
            <input name="tahun_ajaran" value="{{ $year }}" readonly
                   class="rounded-lg border border-slate-200 bg-slate-50 px-3 py-2 text-sm w-32 text-slate-500">
        </div>
        <div>
            <label class="block text-xs text-slate-500 mb-1">Semester</label>
            <select name="semester" class="rounded-lg border border-slate-300 px-3 py-2 text-sm">
                <option value="1" {{ $semester === '1' ? 'selected' : '' }}>1 (Ganjil)</option>
                <option value="2" {{ $semester === '2' ? 'selected' : '' }}>2 (Genap)</option>
            </select>
        </div>
        <button class="rounded-lg bg-slate-200 hover:bg-slate-300 px-4 py-2 text-sm font-medium">Terapkan</button>
        <a href="{{ route('reports.index', request()->query()) }}"
           class="text-xs text-indigo-600 hover:underline self-center">Cetak rapor &rarr;</a>
    </form>

    <div class="mb-4 flex items-center gap-4 text-xs text-slate-500">
        <span class="flex items-center gap-1.5">
            <span class="inline-block w-3 h-3 rounded-sm bg-rose-500"></span>
            Merah = nilai akhir TURUN dibanding semester/tahun ajaran sebelumnya
        </span>
        <span class="flex items-center gap-1.5">
            <span class="inline-block w-3 h-3 rounded-sm bg-emerald-500"></span>
            Hijau = naik / stabil
        </span>
    </div>

    @if($classroom)
        @php
            $subjectColumns = $recap->first()?->subjects->pluck('subject') ?? collect();
        @endphp

        <div class="bg-white rounded-xl shadow-sm border border-slate-200 overflow-x-auto">
            <table class="w-full text-sm">
                <thead class="bg-slate-50 text-slate-500 text-xs uppercase">
                    <tr>
                        <th class="text-left px-4 py-3 sticky left-0 bg-slate-50">Nama Siswa</th>
                        @foreach($subjectColumns as $col)
                            <th class="text-center px-3 py-3" title="{{ $col->name }}">{{ \Illuminate\Support\Str::limit($col->name, 16) }}</th>
                        @endforeach
                    </tr>
                </thead>
                <tbody>
                    @forelse($recap as $row)
                        <tr class="border-t border-slate-100">
                            <td class="px-4 py-2.5 font-medium whitespace-nowrap sticky left-0 bg-white">{{ $row->student->nama }}</td>
                            @foreach($row->subjects as $cell)
                                <td class="px-3 py-2.5 text-center">
                                    @if($cell->final !== null)
                                        <span class="{{ $cell->decreased ? 'text-rose-600 font-bold' : 'text-slate-700' }}"
                                              @if($cell->decreased)
                                                  title="Turun dari {{ $cell->previous }} pada semester/tahun ajaran sebelumnya"
                                              @endif>
                                            {{ rtrim(rtrim(number_format($cell->final, 1, '.', ''), '0'), '.') }}
                                        </span>
                                        @if($cell->decreased)
                                            <span class="text-rose-500 text-[10px] ml-0.5">&#9660;</span>
                                        @endif
                                    @else
                                        <span class="text-slate-300">-</span>
                                    @endif
                                </td>
                            @endforeach
                        </tr>
                    @empty
                        <tr><td colspan="{{ $subjectColumns->count() + 1 }}" class="px-4 py-8 text-center text-slate-400">
                            Belum ada data nilai untuk kelas ini.
                        </td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        <p class="mt-3 text-xs text-slate-400">
            Nilai akhir = (rata-rata formatif &times; {{ $settings->formatif_weight }}%) +
            (rata-rata sumatif &times; {{ $settings->sumatif_weight }}%).
            Pembanding: semester sebelumnya pada tahun ajaran berikutnya (Genap &rarr; Ganjil tahun sama;
            Ganjil &rarr; Genap tahun ajaran sebelumnya).
        </p>
    @else
        <div class="rounded-lg bg-slate-50 border border-slate-200 px-4 py-6 text-center text-sm text-slate-400">
            Pilih kelas terlebih dahulu.
        </div>
    @endif
@endsection
