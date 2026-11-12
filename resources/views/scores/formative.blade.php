@extends('layouts.app')

@section('title', 'Input Nilai Formatif (per TP)')

@section('content')
    <form method="GET" class="bg-white rounded-xl shadow-sm border border-slate-200 p-4 mb-5 flex flex-wrap gap-3 items-end">
        <div>
            <label class="block text-xs text-slate-500 mb-1">Kelas Aktif (dari Pengaturan)</label>
            <div class="rounded-lg border border-slate-200 bg-slate-50 px-3 py-2 text-sm text-slate-700 font-medium">
                Kelas {{ $classroom->name }}
                <a href="{{ route('settings.show') }}" class="ml-2 text-[11px] text-indigo-600 hover:underline font-normal">ubah</a>
            </div>
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
        <button class="rounded-lg bg-slate-200 hover:bg-slate-300 px-4 py-2 text-sm font-medium">Tampilkan</button>
    </form>

    @if($classroom && $subject)
        @if($objectives->isEmpty())
            <div class="rounded-lg bg-amber-50 border border-amber-200 px-4 py-3 text-sm text-amber-800">
                Belum ada Tujuan Pembelajaran untuk mapel
                <b>{{ $subject->name }}</b> di kelas <b>{{ $classroom->name }}</b>
                periode {{ $year }} semester {{ $semester }}.
                Buat dulu di menu <a class="underline font-semibold" href="{{ route('learning-objectives.index') }}">Tujuan Pembelajaran</a>.
            </div>
        @else
            <div class="rounded-lg bg-indigo-50 border border-indigo-200 px-4 py-3 text-sm text-indigo-800 mb-4">
                Nilai formatif per TP digabung dengan nilai sumatif menjadi <b>nilai akhir</b>
                (bobot formatif {{ $settings->formatif_weight }}% / sumatif {{ $settings->sumatif_weight }}%).
                Nilai yang <span class="text-rose-600 font-bold">merah</span> = turun dari semester/tahun sebelumnya.
            </div>

            <form method="POST" action="{{ route('scores.formative.store') }}">
                @csrf
                <input type="hidden" name="subject_id" value="{{ $subject->id }}">
                <input type="hidden" name="tahun_ajaran" value="{{ $year }}">
                <input type="hidden" name="semester" value="{{ $semester }}">

                <div class="bg-white rounded-xl shadow-sm border border-slate-200 overflow-x-auto">
                    <table class="w-full text-sm">
                        <thead class="bg-slate-50 text-slate-500 text-xs uppercase">
                            <tr>
                                <th class="text-left px-4 py-3 sticky left-0 bg-slate-50">No</th>
                                <th class="text-left px-4 py-3 sticky left-16 bg-slate-50">Nama Siswa</th>
                                @foreach($objectives as $objective)
                                    <th class="text-center px-3 py-3" title="{{ $objective->description }}">
                                        <div class="font-semibold text-indigo-700">{{ $objective->code }}</div>
                                        <div class="text-[10px] normal-case font-normal text-slate-400 max-w-[120px] truncate">
                                            {{ \Illuminate\Support\Str::limit($objective->description, 40) }}
                                        </div>
                                    </th>
                                @endforeach
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($students->values() as $index => $student)
                                @php
                                    $studentScores = $scores->get($student->id, collect());
                                    $scoreByTp = $studentScores->keyBy('learning_objective_id');
                                @endphp
                                <tr class="border-t border-slate-100">
                                    <td class="px-4 py-2 text-slate-400 sticky left-0 bg-white">{{ $index + 1 }}</td>
                                    <td class="px-4 py-2 font-medium whitespace-nowrap sticky left-16 bg-white">{{ $student->nama }}</td>
                                    @foreach($objectives as $objective)
                                        @php $value = $scoreByTp->get($objective->id)?->score; @endphp
                                        <td class="px-2 py-2 text-center">
                                            <input type="number" name="rows[{{ $student->id }}][{{ $objective->id }}]"
                                                   value="{{ $value !== null ? rtrim(rtrim(number_format($value, 2, '.', ''), '0'), '.') : '' }}"
                                                   min="0" max="100" step="0.01" placeholder="-"
                                                   class="w-20 rounded-md border border-slate-200 px-2 py-1.5 text-center text-sm focus:outline-none focus:ring-2 focus:ring-indigo-400">
                                        </td>
                                    @endforeach
                                </tr>
                            @endforeach
                            @if($students->isEmpty())
                                <tr><td colspan="{{ $objectives->count() + 2 }}" class="px-4 py-8 text-center text-slate-400">
                                    Tidak ada siswa aktif di kelas ini.
                                </td></tr>
                            @endif
                        </tbody>
                    </table>
                </div>

                <div class="mt-4 flex gap-3">
                    <button class="rounded-lg bg-indigo-600 hover:bg-indigo-700 text-white px-5 py-2 text-sm font-medium">
                        Simpan Nilai Formatif
                    </button>
                    <span class="text-xs text-slate-400 self-center">Kosongkan input untuk menghapus nilai.</span>
                </div>
            </form>
        @endif
    @else
        <div class="rounded-lg bg-slate-50 border border-slate-200 px-4 py-6 text-center text-sm text-slate-400">
            Pilih mata pelajaran terlebih dahulu.
        </div>
    @endif
@endsection
