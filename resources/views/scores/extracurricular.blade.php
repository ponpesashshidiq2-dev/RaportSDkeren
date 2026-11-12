@extends('layouts.app')

@section('title', 'Input Nilai Ekstrakurikuler')

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
            <label class="block text-xs text-slate-500 mb-1">Kegiatan</label>
            <select name="extracurricular_id" required class="rounded-lg border border-slate-300 px-3 py-2 text-sm max-w-xs">
                <option value="">-- Pilih --</option>
                @foreach($extracurricularList as $ex)
                    <option value="{{ $ex->id }}" {{ $extracurricular?->id == $ex->id ? 'selected' : '' }}>{{ $ex->name }}</option>
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
        <a href="{{ route('extracurriculars.index') }}" class="text-xs text-indigo-600 hover:underline self-center">
            Kelola jenis kegiatan &rarr;
        </a>
    </form>

    @if($classroom && $extracurricular)
        <div class="rounded-lg bg-amber-50 border border-amber-200 px-4 py-3 text-sm text-amber-800 mb-4">
            Jika nilai ekstrakurikuler siswa <b>tidak diinput / dikosongkan</b>, maka bagian ekstrakurikuler
            <b>tidak akan muncul sama sekali</b> saat rapor dicetak.
        </div>

        <form method="POST" action="{{ route('scores.extracurricular.store') }}">
            @csrf
            <input type="hidden" name="extracurricular_id" value="{{ $extracurricular->id }}">
            <input type="hidden" name="tahun_ajaran" value="{{ $year }}">
            <input type="hidden" name="semester" value="{{ $semester }}">

            <div class="bg-white rounded-xl shadow-sm border border-slate-200 overflow-x-auto">
                <table class="w-full text-sm">
                    <thead class="bg-slate-50 text-slate-500 text-xs uppercase">
                        <tr>
                            <th class="text-left px-4 py-3">No</th>
                            <th class="text-left px-4 py-3">Nama Siswa</th>
                            <th class="text-left px-4 py-3">Predikat / Nilai</th>
                        </tr>
                    </thead>
                    <tbody>
                        @php
                            $predikatOptions = ['Sangat Baik', 'Baik', 'Cukup', 'Kurang', 'Berprestasi', 'Aktif', 'Perlu Bimbingan'];
                        @endphp
                        @forelse($students as $index => $student)
                            @php $current = $scores->get($student->id)?->predikat; @endphp
                            <tr class="border-t border-slate-100">
                                <td class="px-4 py-2.5 text-slate-400">{{ $index + 1 }}</td>
                                <td class="px-4 py-2.5 font-medium">{{ $student->nama }}</td>
                                <td class="px-4 py-2.5">
                                    <input name="rows[{{ $student->id }}]" list="predikat-options"
                                           value="{{ $current }}" placeholder="(kosong = tidak dicetak)"
                                           class="w-64 rounded-md border border-slate-200 px-3 py-1.5 text-sm focus:outline-none focus:ring-2 focus:ring-indigo-400">
                                </td>
                            </tr>
                        @empty
                            <tr><td colspan="3" class="px-4 py-8 text-center text-slate-400">Tidak ada siswa aktif di kelas ini.</td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            <datalist id="predikat-options">
                @foreach($predikatOptions as $option)
                    <option value="{{ $option }}"></option>
                @endforeach
            </datalist>

            <div class="mt-4">
                <button class="rounded-lg bg-indigo-600 hover:bg-indigo-700 text-white px-5 py-2 text-sm font-medium">
                    Simpan Nilai Ekstrakurikuler
                </button>
            </div>
        </form>
    @else
        <div class="rounded-lg bg-slate-50 border border-slate-200 px-4 py-6 text-center text-sm text-slate-400">
            Pilih kelas dan kegiatan ekstrakurikuler terlebih dahulu.
        </div>
    @endif
@endsection
