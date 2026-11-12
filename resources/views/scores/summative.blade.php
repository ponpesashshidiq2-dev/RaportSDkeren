@extends('layouts.app')

@section('title', 'Input Nilai Sumatif')

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
            <label class="block text-xs text-slate-500 mb-1">Mata Pelajaran</label>
            <select name="subject_id" required class="rounded-lg border border-slate-300 px-3 py-2 text-sm max-w-xs">
                <option value="">-- Pilih --</option>
                @foreach($subjects as $sub)
                    <option value="{{ $sub->id }}" {{ $subject?->id == $sub->id ? 'selected' : '' }}>{{ $sub->name }}</option>
                @endforeach
            </select>
        </div>
        <div>
            <label class="block text-xs text-slate-500 mb-1">Semester</label>
            <select name="semester" class="rounded-lg border border-slate-300 px-3 py-2 text-sm">
                <option value="1" {{ $semester === '1' ? 'selected' : '' }}>1 (Ganjil)</option>
                <option value="2" {{ $semester === '2' ? 'selected' : '' }}>2 (Genap)</option>
            </select>
        </div>
        <input type="hidden" name="tahun_ajaran" value="{{ $year }}">
        <button class="rounded-lg bg-slate-200 hover:bg-slate-300 px-4 py-2 text-sm font-medium">Tampilkan</button>
    </form>

    @if($classroom && $subject)
        <form method="POST" action="{{ route('scores.summative.store') }}">
            @csrf
            <input type="hidden" name="classroom_id" value="{{ $classroom->id }}">
            <input type="hidden" name="subject_id" value="{{ $subject->id }}">
            <input type="hidden" name="tahun_ajaran" value="{{ $year }}">
            <input type="hidden" name="semester" value="{{ $semester }}">

            <div class="bg-white rounded-xl shadow-sm border border-slate-200 p-4 mb-4 flex flex-wrap gap-4 items-end">
                <div class="flex-1 min-w-[280px]">
                    <label class="block text-xs text-slate-500 mb-1">
                        Lingkup Materi / Akhir Semester <span class="text-rose-500">*</span>
                    </label>
                    <input name="lingkup_materi" list="lingkup-options" required
                           value="{{ old('lingkup_materi') }}"
                           placeholder="Misal: Lingkup Materi Bab 1-3 atau Akhir Semester"
                           class="w-full rounded-lg border border-slate-300 px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-indigo-500">
                    <datalist id="lingkup-options">
                        @foreach($lingkupOptions as $lingkup)
                            <option value="{{ $lingkup }}"></option>
                        @endforeach
                    </datalist>
                </div>
                <div class="text-xs text-slate-400 pb-2">
                    Nilai akhir = gabungan rata-rata formatif &amp; sumatif.
                </div>
            </div>

            <div class="bg-white rounded-xl shadow-sm border border-slate-200 overflow-x-auto">
                <table class="w-full text-sm">
                    <thead class="bg-slate-50 text-slate-500 text-xs uppercase">
                        <tr>
                            <th class="text-left px-4 py-3">No</th>
                            <th class="text-left px-4 py-3">Nama Siswa</th>
                            <th class="text-center px-4 py-3 w-56">Nilai Sumatif (0-100)</th>
                            <th class="text-left px-4 py-3">Nilai Terakhir Tersimpan</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($students as $index => $student)
                            @php
                                $existing = $scores->get($student->id, collect());
                                $latest = $existing->sortByDesc('assessed_at')->first();
                            @endphp
                            <tr class="border-t border-slate-100">
                                <td class="px-4 py-2.5 text-slate-400">{{ $index + 1 }}</td>
                                <td class="px-4 py-2.5 font-medium">{{ $student->nama }}</td>
                                <td class="px-4 py-2.5 text-center">
                                    <input type="number" name="rows[{{ $student->id }}]"
                                           value="{{ $latest?->score !== null ? rtrim(rtrim(number_format($latest->score, 2, '.', ''), '0'), '.') : '' }}"
                                           min="0" max="100" step="0.01" placeholder="-"
                                           class="w-28 rounded-md border border-slate-200 px-2 py-1.5 text-center text-sm focus:outline-none focus:ring-2 focus:ring-indigo-400">
                                </td>
                                <td class="px-4 py-2.5 text-xs text-slate-500">
                                    @if($latest)
                                        {{ $latest->score }} <span class="text-slate-300">({{ $latest->lingkup_materi }})</span>
                                    @else
                                        <span class="text-slate-300">Belum ada</span>
                                    @endif
                                </td>
                            </tr>
                        @empty
                            <tr><td colspan="4" class="px-4 py-8 text-center text-slate-400">Tidak ada siswa aktif di kelas ini.</td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            <div class="mt-4">
                <button class="rounded-lg bg-indigo-600 hover:bg-indigo-700 text-white px-5 py-2 text-sm font-medium">
                    Simpan Nilai Sumatif
                </button>
            </div>
        </form>
    @else
        <div class="rounded-lg bg-slate-50 border border-slate-200 px-4 py-6 text-center text-sm text-slate-400">
            Pilih kelas dan mata pelajaran terlebih dahulu.
        </div>
    @endif
@endsection
