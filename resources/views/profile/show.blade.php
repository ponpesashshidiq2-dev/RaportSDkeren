@extends('layouts.app')

@section('title', 'Profil Saya')

@section('content')
    <div class="grid lg:grid-cols-3 gap-5">
        {{-- Kartu akun --}}
        <div class="bg-white rounded-xl shadow-sm border border-slate-200 p-5">
            <div class="flex items-center gap-3 mb-4">
                <div class="h-14 w-14 rounded-full bg-indigo-100 text-indigo-700 flex items-center justify-center text-xl font-bold">
                    {{ strtoupper(substr($user->name, 0, 1)) }}
                </div>
                <div>
                    <div class="font-semibold text-slate-800">{{ $user->name }}</div>
                    <div class="text-xs text-slate-500">{{ $user->roleLabel() }}</div>
                </div>
            </div>

            <dl class="text-sm space-y-2">
                <div class="flex justify-between gap-3 border-t border-slate-100 pt-2">
                    <dt class="text-slate-500">Email</dt>
                    <dd class="font-medium text-right break-all">{{ $user->email }}</dd>
                </div>
                <div class="flex justify-between gap-3 border-t border-slate-100 pt-2">
                    <dt class="text-slate-500">No. HP</dt>
                    <dd class="font-medium text-right">{{ $user->phone ?? '-' }}</dd>
                </div>
                @if($user->classroom)
                    <div class="flex justify-between gap-3 border-t border-slate-100 pt-2">
                        <dt class="text-slate-500">Rombel</dt>
                        <dd class="font-medium text-right">{{ $user->classroom->name }}</dd>
                    </div>
                @endif
                <div class="flex justify-between gap-3 border-t border-slate-100 pt-2">
                    <dt class="text-slate-500">Bergabung</dt>
                    <dd class="font-medium text-right">{{ optional($user->created_at)->translatedFormat('d M Y') }}</dd>
                </div>
            </dl>

            <div class="mt-4 flex flex-wrap gap-2">
                @if($user->role === 'siswa_orangtua')
                    <a href="{{ route('my-scores.index') }}"
                       class="rounded-lg bg-indigo-600 hover:bg-indigo-700 text-white px-4 py-2 text-sm font-medium">Nilai Saya</a>
                    <a href="{{ route('reports.index') }}"
                       class="rounded-lg border border-indigo-300 text-indigo-700 hover:bg-indigo-50 px-4 py-2 text-sm font-medium">Rapor Saya</a>
                @endif
            </div>
        </div>

        {{-- Kartu data siswa --}}
        <div class="lg:col-span-2 bg-white rounded-xl shadow-sm border border-slate-200 p-5">
            <h2 class="font-semibold text-slate-700 mb-4">Data Peserta Didik</h2>

            @if(! $student)
                <div class="rounded-lg bg-amber-50 border border-amber-200 px-4 py-3 text-sm text-amber-800">
                    Akun ini belum tertaut ke data siswa.
                    @if(auth()->user()->role === 'super_admin')
                        Tautkan melalui menu <a href="{{ route('users.index') }}" class="font-semibold underline">Kelola Pengguna</a>.
                    @else
                        Hubungi administrator sekolah untuk menautkan.
                    @endif
                </div>
            @else
                @if(! $student->is_active)
                    <div class="mb-3 rounded-lg bg-rose-50 border border-rose-200 px-4 py-3 text-sm text-rose-800">
                        Status siswa: <strong>Nonaktif</strong> — data rapor tidak dapat diakses.
                    </div>
                @endif

                <div class="grid sm:grid-cols-2 gap-x-6 text-sm">
                    <dl class="space-y-2">
                        <div class="flex justify-between gap-3 border-b border-slate-100 pb-2">
                            <dt class="text-slate-500">NISN</dt>
                            <dd class="font-mono font-medium">{{ $student->nisn ?? '-' }}</dd>
                        </div>
                        <div class="flex justify-between gap-3 border-b border-slate-100 pb-2">
                            <dt class="text-slate-500">NIS</dt>
                            <dd class="font-mono font-medium">{{ $student->nis ?? '-' }}</dd>
                        </div>
                        <div class="flex justify-between gap-3 border-b border-slate-100 pb-2">
                            <dt class="text-slate-500">Nama</dt>
                            <dd class="font-medium text-right">{{ $student->nama }}</dd>
                        </div>
                        <div class="flex justify-between gap-3 border-b border-slate-100 pb-2">
                            <dt class="text-slate-500">Jenis Kelamin</dt>
                            <dd class="font-medium">{{ $student->jenis_kelamin_label }}</dd>
                        </div>
                        <div class="flex justify-between gap-3 border-b border-slate-100 pb-2">
                            <dt class="text-slate-500">Tempat, Tgl Lahir</dt>
                            <dd class="font-medium text-right">{{ $student->ttl ?? trim(($student->tempat_lahir ?? '').', '.($student->tanggal_lahir?->translatedFormat('d F Y') ?? '')) }}</dd>
                        </div>
                        <div class="flex justify-between gap-3 border-b border-slate-100 pb-2">
                            <dt class="text-slate-500">No. HP</dt>
                            <dd class="font-medium">{{ $student->no_hp ?? '-' }}</dd>
                        </div>
                    </dl>

                    <dl class="space-y-2">
                        <div class="flex justify-between gap-3 border-b border-slate-100 pb-2">
                            <dt class="text-slate-500">Kelas</dt>
                            <dd class="font-medium">{{ $student->classroom?->name ?? '-' }}</dd>
                        </div>
                        <div class="flex justify-between gap-3 border-b border-slate-100 pb-2">
                            <dt class="text-slate-500">Tahun Ajaran</dt>
                            <dd class="font-medium">{{ $student->classroom?->academic_year ?? '-' }}</dd>
                        </div>
                        <div class="flex justify-between gap-3 border-b border-slate-100 pb-2">
                            <dt class="text-slate-500">Nama Ayah</dt>
                            <dd class="font-medium text-right">{{ $student->nama_ayah ?? '-' }}</dd>
                        </div>
                        <div class="flex justify-between gap-3 border-b border-slate-100 pb-2">
                            <dt class="text-slate-500">Nama Ibu</dt>
                            <dd class="font-medium text-right">{{ $student->nama_ibu ?? '-' }}</dd>
                        </div>
                        <div class="flex justify-between gap-3 border-b border-slate-100 pb-2">
                            <dt class="text-slate-500">Orang Tua/Wali</dt>
                            <dd class="font-medium text-right">{{ $student->nama_wali ?? '-' }}</dd>
                        </div>
                        <div class="flex justify-between gap-3 border-b border-slate-100 pb-2">
                            <dt class="text-slate-500">Alamat</dt>
                            <dd class="font-medium text-right max-w-[240px]">{{ $student->alamat ?? '-' }}</dd>
                        </div>
                    </dl>
                </div>
            @endif
        </div>

        {{-- Aktivitas login --}}
        <div class="lg:col-span-3 bg-white rounded-xl shadow-sm border border-slate-200 p-5">
            <h2 class="font-semibold text-slate-700 mb-3">Aktivitas Login Terakhir</h2>
            @if($recentLogins->isEmpty())
                <p class="text-sm text-slate-400">Belum ada riwayat login.</p>
            @else
                <div class="overflow-x-auto">
                    <table class="w-full text-sm">
                        <thead class="bg-slate-50 text-slate-500 text-xs uppercase">
                            <tr>
                                <th class="text-left px-4 py-2">Waktu</th>
                                <th class="text-left px-4 py-2">Alamat IP</th>
                                <th class="text-left px-4 py-2">Perangkat</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($recentLogins as $log)
                                <tr class="border-t border-slate-100">
                                    <td class="px-4 py-2">{{ optional($log->logged_in_at)->translatedFormat('d M Y H:i') }}</td>
                                    <td class="px-4 py-2 font-mono text-xs">{{ $log->ip_address ?? '-' }}</td>
                                    <td class="px-4 py-2 text-xs text-slate-500 max-w-[320px] truncate" title="{{ $log->user_agent ?? '' }}">
                                        {{ $log->user_agent ?? '-' }}
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            @endif
        </div>
    </div>
@endsection
