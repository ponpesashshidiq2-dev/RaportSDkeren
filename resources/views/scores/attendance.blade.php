@extends('layouts.app')

@section('title', 'Input Kehadiran Siswa')

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
    </form>

    @if($classroom)
        <form method="POST" action="{{ route('scores.attendance.store') }}">
            @csrf
            <input type="hidden" name="tahun_ajaran" value="{{ $year }}">
            <input type="hidden" name="semester" value="{{ $semester }}">

            <div class="bg-white rounded-xl shadow-sm border border-slate-200 overflow-x-auto">
                <table class="w-full text-sm">
                    <thead class="bg-slate-50 text-slate-500 text-xs uppercase">
                        <tr>
                            <th class="text-left px-4 py-3">No</th>
                            <th class="text-left px-4 py-3">Nama Siswa</th>
                            <th class="text-center px-4 py-3">Sakit</th>
                            <th class="text-center px-4 py-3">Izin</th>
                            <th class="text-center px-4 py-3">Tanpa Keterangan</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($students as $index => $student)
                            @php $att = $attendances->get($student->id); @endphp
                            <tr class="border-t border-slate-100">
                                <td class="px-4 py-2.5 text-slate-400">{{ $index + 1 }}</td>
                                <td class="px-4 py-2.5 font-medium">{{ $student->nama }}</td>
                                <td class="px-4 py-2.5 text-center">
                                    <input type="number" name="rows[{{ $student->id }}][sakit]"
                                           value="{{ $att?->sakit ?? 0 }}" min="0" max="365"
                                           class="w-20 rounded-md border border-slate-200 px-2 py-1.5 text-center text-sm focus:outline-none focus:ring-2 focus:ring-indigo-400">
                                </td>
                                <td class="px-4 py-2.5 text-center">
                                    <input type="number" name="rows[{{ $student->id }}][izin]"
                                           value="{{ $att?->izin ?? 0 }}" min="0" max="365"
                                           class="w-20 rounded-md border border-slate-200 px-2 py-1.5 text-center text-sm focus:outline-none focus:ring-2 focus:ring-indigo-400">
                                </td>
                                <td class="px-4 py-2.5 text-center">
                                    <input type="number" name="rows[{{ $student->id }}][tanpa_keterangan]"
                                           value="{{ $att?->tanpa_keterangan ?? 0 }}" min="0" max="365"
                                           class="w-20 rounded-md border border-slate-200 px-2 py-1.5 text-center text-sm focus:outline-none focus:ring-2 focus:ring-indigo-400">
                                </td>
                            </tr>
                        @empty
                            <tr><td colspan="5" class="px-4 py-8 text-center text-slate-400">Tidak ada siswa aktif di kelas ini.</td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            <div class="mt-4">
                <button class="rounded-lg bg-indigo-600 hover:bg-indigo-700 text-white px-5 py-2 text-sm font-medium">
                    Simpan Kehadiran
                </button>
            </div>
        </form>
    @else
        <div class="rounded-lg bg-slate-50 border border-slate-200 px-4 py-6 text-center text-sm text-slate-400">
            Pilih kelas terlebih dahulu.
        </div>
    @endif
@endsection
