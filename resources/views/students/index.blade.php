@extends('layouts.app')

@section('title', 'Data Siswa')

@section('content')
    <div class="flex flex-wrap items-center justify-between gap-3 mb-5">
        <form method="GET" class="flex flex-wrap gap-2 items-end">
            <div>
                <label class="block text-xs text-slate-500 mb-1">Cari</label>
                <input type="text" name="q" value="{{ request('q') }}" placeholder="Nama / NISN / NIS"
                       class="rounded-lg border border-slate-300 px-3 py-2 text-sm w-52 focus:outline-none focus:ring-2 focus:ring-indigo-500">
            </div>
            <div>
                <label class="block text-xs text-slate-500 mb-1">Kelas</label>
                <select name="kelas_id" class="rounded-lg border border-slate-300 px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-indigo-500">
                    <option value="">Semua kelas</option>
                    @foreach($classrooms as $rombel)
                        <option value="{{ $rombel->id }}" {{ request('kelas_id') == $rombel->id ? 'selected' : '' }}>
                            {{ $rombel->name }}
                        </option>
                    @endforeach
                </select>
            </div>
            <button class="rounded-lg bg-slate-200 hover:bg-slate-300 px-4 py-2 text-sm font-medium">Filter</button>
        </form>

        <div class="flex gap-2">
            <a href="{{ route('students.import') }}"
               class="rounded-lg border border-indigo-300 text-indigo-700 hover:bg-indigo-50 px-4 py-2 text-sm font-medium">
                Import Excel
            </a>
            <a href="{{ route('students.create') }}"
               class="rounded-lg bg-indigo-600 hover:bg-indigo-700 text-white px-4 py-2 text-sm font-medium">
                + Tambah Siswa
            </a>
        </div>
    </div>

    <div class="bg-white rounded-xl shadow-sm border border-slate-200 overflow-x-auto">
        <table class="w-full text-sm">
            <thead class="bg-slate-50 text-slate-500 text-xs uppercase">
                <tr>
                    <th class="text-left px-4 py-3">NISN</th>
                    <th class="text-left px-4 py-3">NIS</th>
                    <th class="text-left px-4 py-3">Nama Peserta Didik</th>
                    <th class="text-left px-4 py-3">JK</th>
                    <th class="text-left px-4 py-3">Tempat, Tanggal Lahir</th>
                    <th class="text-left px-4 py-3">Kelas</th>
                    <th class="text-left px-4 py-3">Alamat</th>
                    <th class="text-right px-4 py-3">Aksi</th>
                </tr>
            </thead>
            <tbody>
                @forelse($students as $student)
                    <tr class="border-t border-slate-100 hover:bg-slate-50">
                        <td class="px-4 py-3 font-mono text-xs">{{ $student->nisn ?? '-' }}</td>
                        <td class="px-4 py-3 font-mono text-xs">{{ $student->nis ?? '-' }}</td>
                        <td class="px-4 py-3 font-medium text-slate-800">
                            {{ $student->nama }}
                            @unless($student->is_active)
                                <span class="ml-1 text-[10px] uppercase bg-slate-200 text-slate-500 rounded px-1.5 py-0.5">Nonaktif</span>
                            @endunless
                        </td>
                        <td class="px-4 py-3">{{ $student->jenis_kelamin }}</td>
                        <td class="px-4 py-3 text-slate-500 text-xs">{{ $student->ttl }}</td>
                        <td class="px-4 py-3">{{ $student->classroom?->name ?? '-' }}</td>
                        <td class="px-4 py-3 text-slate-500 text-xs max-w-[220px] truncate" title="{{ $student->alamat }}">
                            {{ $student->alamat }}
                        </td>
                        <td class="px-4 py-3 text-right whitespace-nowrap">
                            <a href="{{ route('students.edit', $student) }}" class="text-indigo-600 hover:underline text-xs font-semibold">Ubah</a>
                            <form action="{{ route('students.destroy', $student) }}" method="POST" class="inline"
                                  onsubmit="return confirm('Hapus data {{ $student->nama }}?')">
                                @csrf @method('DELETE')
                                <button class="text-rose-600 hover:underline text-xs font-semibold ml-3">Hapus</button>
                            </form>
                        </td>
                    </tr>
                @empty
                    <tr><td colspan="8" class="px-4 py-8 text-center text-slate-400">Belum ada data siswa.</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>

    <div class="mt-4">
        {{ $students->links() }}
    </div>
@endsection
