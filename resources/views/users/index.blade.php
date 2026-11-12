@extends('layouts.app')

@section('title', 'Kelola Pengguna')

@section('content')
    <div class="flex flex-wrap items-center justify-between gap-3 mb-5">
        <form method="GET" class="flex flex-wrap gap-2 items-end">
            <div>
                <label class="block text-xs text-slate-500 mb-1">Cari</label>
                <input type="text" name="q" value="{{ request('q') }}" placeholder="Nama / email"
                       class="rounded-lg border border-slate-300 px-3 py-2 text-sm w-52 focus:outline-none focus:ring-2 focus:ring-indigo-500">
            </div>
            <div>
                <label class="block text-xs text-slate-500 mb-1">Role</label>
                <select name="role" class="rounded-lg border border-slate-300 px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-indigo-500">
                    <option value="">Semua role</option>
                    @foreach($roleLabels as $key => $label)
                        <option value="{{ $key }}" {{ request('role') === $key ? 'selected' : '' }}>{{ $label }}</option>
                    @endforeach
                </select>
            </div>
            <button class="rounded-lg bg-slate-200 hover:bg-slate-300 px-4 py-2 text-sm font-medium">Filter</button>
        </form>

        <a href="{{ route('users.create') }}"
           class="rounded-lg bg-indigo-600 hover:bg-indigo-700 text-white px-4 py-2 text-sm font-medium">
            + Tambah Pengguna
        </a>
    </div>

    <div class="bg-white rounded-xl shadow-sm border border-slate-200 overflow-x-auto">
        <table class="w-full text-sm">
            <thead class="bg-slate-50 text-slate-500 text-xs uppercase">
                <tr>
                    <th class="text-left px-4 py-3">Nama</th>
                    <th class="text-left px-4 py-3">Email</th>
                    <th class="text-left px-4 py-3">Role</th>
                    <th class="text-left px-4 py-3">Tautan Data</th>
                    <th class="text-right px-4 py-3">Aksi</th>
                </tr>
            </thead>
            <tbody>
                @forelse($users as $user)
                    <tr class="border-t border-slate-100 hover:bg-slate-50">
                        <td class="px-4 py-3 font-medium text-slate-800">
                            {{ $user->name }}
                            @if($user->id === auth()->id())
                                <span class="ml-1 text-[10px] uppercase bg-indigo-100 text-indigo-700 rounded px-1.5 py-0.5">Anda</span>
                            @endif
                        </td>
                        <td class="px-4 py-3 text-slate-600">{{ $user->email }}</td>
                        <td class="px-4 py-3">
                            @php
                                $badges = [
                                    'super_admin' => 'bg-purple-100 text-purple-700',
                                    'guru' => 'bg-sky-100 text-sky-700',
                                    'wali_kelas' => 'bg-indigo-100 text-indigo-700',
                                    'kepala_sekolah' => 'bg-emerald-100 text-emerald-700',
                                    'siswa_orangtua' => 'bg-amber-100 text-amber-700',
                                ];
                                $badge = $badges[$user->role] ?? 'bg-slate-100 text-slate-600';
                            @endphp
                            <span class="text-[11px] uppercase rounded px-2 py-0.5 font-semibold {{ $badge }}">
                                {{ $roleLabels[$user->role] ?? $user->role }}
                            </span>
                        </td>
                        <td class="px-4 py-3 text-xs text-slate-500">
                            @if($user->student)
                                <span class="text-indigo-700 font-medium">Siswa:</span> {{ $user->student->nama }}
                                <span class="text-slate-400">(Kelas {{ $user->student->classroom?->name ?? '-' }})</span>
                            @elseif($user->classroom)
                                <span class="text-indigo-700 font-medium">Rombel:</span> {{ $user->classroom->name }}
                            @else
                                <span class="text-slate-400">—</span>
                            @endif
                        </td>
                        <td class="px-4 py-3 text-right whitespace-nowrap">
                            <a href="{{ route('users.edit', $user) }}" class="text-indigo-600 hover:underline text-xs font-semibold">Ubah</a>
                            @if($user->id !== auth()->id())
                                <form action="{{ route('users.destroy', $user) }}" method="POST" class="inline"
                                      onsubmit="return confirm('Hapus pengguna {{ $user->email }}?')">
                                    @csrf @method('DELETE')
                                    <button class="text-rose-600 hover:underline text-xs font-semibold ml-3">Hapus</button>
                                </form>
                            @endif
                        </td>
                    </tr>
                @empty
                    <tr><td colspan="5" class="px-4 py-8 text-center text-slate-400">Belum ada pengguna.</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>

    <div class="mt-4">
        {{ $users->links() }}
    </div>
@endsection
