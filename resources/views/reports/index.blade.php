@extends('layouts.app')

@section('title', 'Cetak Rapor')

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
        <a href="{{ route('reports.recap', request()->query()) }}"
           class="text-xs text-indigo-600 hover:underline self-center">Rekap nilai (merah = turun) &rarr;</a>
    </form>

    @if($classroom)
        <div class="bg-white rounded-xl shadow-sm border border-slate-200 overflow-x-auto">
            <table class="w-full text-sm">
                <thead class="bg-slate-50 text-slate-500 text-xs uppercase">
                    <tr>
                        <th class="text-left px-5 py-3">No</th>
                        <th class="text-left px-5 py-3">Nama Peserta Didik</th>
                        <th class="text-left px-5 py-3">NISN</th>
                        <th class="text-right px-5 py-3">Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($students as $index => $student)
                        <tr class="border-t border-slate-100 hover:bg-slate-50">
                            <td class="px-5 py-3 text-slate-400">{{ $index + 1 }}</td>
                            <td class="px-5 py-3 font-medium text-slate-800">{{ $student->nama }}</td>
                            <td class="px-5 py-3 font-mono text-xs text-slate-500">{{ $student->nisn ?? '-' }}</td>
                            <td class="px-5 py-3 text-right whitespace-nowrap">
                                <a href="{{ route('reports.show', ['student_id' => $student->id, 'tahun_ajaran' => $year, 'semester' => $semester]) }}"
                                   class="rounded-md bg-slate-100 hover:bg-slate-200 px-3 py-1.5 text-xs font-semibold mr-2">
                                    Preview
                                </a>
                                <a href="{{ route('reports.print', ['student_id' => $student->id, 'tahun_ajaran' => $year, 'semester' => $semester]) }}"
                                   class="rounded-md bg-rose-600 hover:bg-rose-700 text-white px-3 py-1.5 text-xs font-semibold">
                                    Cetak PDF
                                </a>
                            </td>
                        </tr>
                    @empty
                        <tr><td colspan="4" class="px-5 py-8 text-center text-slate-400">Tidak ada siswa pada kelas ini.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        <div class="mt-4 rounded-lg bg-slate-50 border border-slate-200 px-4 py-3 text-xs text-slate-500">
            Catatan: hanya mata pelajaran ber-KKM yang dicetak; bagian Ekstrakurikuler muncul otomatis hanya jika ada nilai.
        </div>
    @else
        <div class="rounded-lg bg-slate-50 border border-slate-200 px-4 py-6 text-center text-sm text-slate-400">
            Pilih kelas terlebih dahulu.
        </div>
    @endif
@endsection
