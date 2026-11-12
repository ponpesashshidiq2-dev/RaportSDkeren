@extends('layouts.app')

@section('title', 'Tujuan Pembelajaran (TP)')

@section('content')
    {{-- Filter periode --}}
    <form method="GET" class="bg-white rounded-xl shadow-sm border border-slate-200 p-4 mb-5 flex flex-wrap gap-3 items-end">
        <div>
            <label class="block text-xs text-slate-500 mb-1">Kelas</label>
            <select name="classroom_id" class="rounded-lg border border-slate-300 px-3 py-2 text-sm">
                <option value="">-- Pilih --</option>
                @foreach($classrooms as $rombel)
                    <option value="{{ $rombel->id }}" {{ ($filters['classroom_id'] ?? null) == $rombel->id ? 'selected' : '' }}>
                        {{ $rombel->name }}
                    </option>
                @endforeach
            </select>
        </div>
        <div>
            <label class="block text-xs text-slate-500 mb-1">Mata Pelajaran</label>
            <select name="subject_id" class="rounded-lg border border-slate-300 px-3 py-2 text-sm max-w-xs">
                <option value="">-- Semua --</option>
                @foreach($subjects as $subject)
                    <option value="{{ $subject->id }}" {{ ($filters['subject_id'] ?? null) == $subject->id ? 'selected' : '' }}>
                        {{ $subject->name }}
                    </option>
                @endforeach
            </select>
        </div>
        <div>
            <label class="block text-xs text-slate-500 mb-1">Tahun Ajaran</label>
            <select name="tahun_ajaran" class="rounded-lg border border-slate-300 px-3 py-2 text-sm">
                @foreach($academicYears as $year)
                    <option value="{{ $year }}" {{ $filters['tahun_ajaran'] === $year ? 'selected' : '' }}>{{ $year }}</option>
                @endforeach
            </select>
        </div>
        <div>
            <label class="block text-xs text-slate-500 mb-1">Semester</label>
            <select name="semester" class="rounded-lg border border-slate-300 px-3 py-2 text-sm">
                <option value="1" {{ $filters['semester'] === '1' ? 'selected' : '' }}>1 (Ganjil)</option>
                <option value="2" {{ $filters['semester'] === '2' ? 'selected' : '' }}>2 (Genap)</option>
            </select>
        </div>
        <button class="rounded-lg bg-slate-200 hover:bg-slate-300 px-4 py-2 text-sm font-medium">Terapkan</button>
        <a href="{{ route('learning-objectives.create', request()->query()) }}"
           class="rounded-lg bg-indigo-600 hover:bg-indigo-700 text-white px-4 py-2 text-sm font-medium">
            + Tambah TP
        </a>
    </form>

    <div class="bg-white rounded-xl shadow-sm border border-slate-200 overflow-x-auto">
        <table class="w-full text-sm">
            <thead class="bg-slate-50 text-slate-500 text-xs uppercase">
                <tr>
                    <th class="text-left px-5 py-3">Kode</th>
                    <th class="text-left px-5 py-3">Mapel</th>
                    <th class="text-left px-5 py-3">Kelas</th>
                    <th class="text-left px-5 py-3">Deskripsi Capaian / Tujuan Pembelajaran</th>
                    <th class="text-left px-5 py-3">Pendekatan (Deep Learning)</th>
                    <th class="text-right px-5 py-3">Aksi</th>
                </tr>
            </thead>
            <tbody>
                @forelse($objectives as $objective)
                    <tr class="border-t border-slate-100 hover:bg-slate-50 align-top">
                        <td class="px-5 py-3 font-mono text-xs font-semibold text-indigo-700">{{ $objective->code }}</td>
                        <td class="px-5 py-3">{{ $objective->subject?->name }}</td>
                        <td class="px-5 py-3">{{ $objective->classroom?->name }}</td>
                        <td class="px-5 py-3 text-xs text-slate-600 max-w-md">{{ $objective->description }}</td>
                        <td class="px-5 py-3 text-xs text-slate-400 max-w-[220px]">{{ $objective->approach ?? '-' }}</td>
                        <td class="px-5 py-3 text-right whitespace-nowrap">
                            <a href="{{ route('learning-objectives.edit', $objective) }}" class="text-indigo-600 hover:underline text-xs font-semibold">Ubah</a>
                            <form action="{{ route('learning-objectives.destroy', $objective) }}" method="POST" class="inline"
                                  onsubmit="return confirm('Hapus TP {{ $objective->code }}?')">
                                @csrf @method('DELETE')
                                <button class="text-rose-600 hover:underline text-xs font-semibold ml-3">Hapus</button>
                            </form>
                        </td>
                    </tr>
                @empty
                    <tr><td colspan="6" class="px-5 py-8 text-center text-slate-400">
                        Belum ada TP untuk periode/kelas tersebut. Klik "+ Tambah TP".
                    </td></tr>
                @endforelse
            </tbody>
        </table>
    </div>
@endsection
