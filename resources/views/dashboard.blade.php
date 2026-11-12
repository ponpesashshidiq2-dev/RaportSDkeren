@extends('layouts.app')

@section('title', 'Dashboard Profil')

@section('content')
    {{-- Ringkasan sekolah --}}
    <div class="bg-white rounded-xl shadow-sm border border-slate-200 p-6 mb-6">
        <div class="flex items-start justify-between">
            <div>
                <h2 class="text-xl font-bold text-slate-800">{{ $settings->school_name }}</h2>
                <p class="text-sm text-slate-500 mt-1">
                    NPSN: {{ $settings->npsn ?? '-' }} &middot;
                    {{ $settings->address ?? '-' }}
                </p>
                <p class="text-sm text-slate-500">
                    Tahun Ajaran Aktif: <span class="font-semibold text-slate-700">{{ $settings->active_academic_year }}</span>
                    &middot; Semester <span class="font-semibold text-slate-700">{{ $settings->semesterLabel() }}</span>
                </p>
            </div>
            <div class="text-right text-xs text-slate-400">
                Kepala Sekolah<br>
                <span class="text-sm text-slate-600 font-semibold">{{ $settings->kepala_sekolah_name ?? '-' }}</span>
            </div>
        </div>
    </div>

    {{-- Statistik --}}
    <div class="grid grid-cols-2 md:grid-cols-3 xl:grid-cols-6 gap-4 mb-6">
        @php
            $cards = [
                ['label' => 'Siswa Aktif', 'value' => $stats['siswa'], 'color' => 'bg-indigo-50 text-indigo-700'],
                ['label' => 'Guru', 'value' => $stats['guru'], 'color' => 'bg-sky-50 text-sky-700'],
                ['label' => 'Rombel', 'value' => $stats['kelas'], 'color' => 'bg-violet-50 text-violet-700'],
                ['label' => 'Mata Pelajaran', 'value' => $stats['mapel'], 'color' => 'bg-amber-50 text-amber-700'],
                ['label' => 'Login Hari Ini', 'value' => $stats['login_hari_ini'], 'color' => 'bg-emerald-50 text-emerald-700'],
                ['label' => 'Total Login', 'value' => $stats['login_total'], 'color' => 'bg-slate-100 text-slate-700'],
            ];
        @endphp
        @foreach($cards as $card)
            <div class="rounded-xl border border-slate-200 bg-white p-4 {{ $card['color'] }}">
                <div class="text-2xl font-bold">{{ $card['value'] }}</div>
                <div class="text-xs font-medium opacity-80 mt-1">{{ $card['label'] }}</div>
            </div>
        @endforeach
    </div>

    <div class="grid lg:grid-cols-2 gap-6">
        {{-- Siswa per kelas --}}
        <div class="bg-white rounded-xl shadow-sm border border-slate-200 overflow-hidden">
            <div class="px-5 py-4 border-b border-slate-200 font-semibold text-slate-700">Siswa per Rombel</div>
            <table class="w-full text-sm">
                <thead class="bg-slate-50 text-slate-500 text-xs uppercase">
                    <tr>
                        <th class="text-left px-5 py-2.5">Kelas</th>
                        <th class="text-left px-5 py-2.5">Tahun Ajaran</th>
                        <th class="text-right px-5 py-2.5">Siswa</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($siswaPerKelas as $rombel)
                        <tr class="border-t border-slate-100">
                            <td class="px-5 py-2.5 font-medium">{{ $rombel->name }}</td>
                            <td class="px-5 py-2.5 text-slate-500">{{ $rombel->academic_year }}</td>
                            <td class="px-5 py-2.5 text-right font-semibold">{{ $rombel->students_count }}</td>
                        </tr>
                    @empty
                        <tr><td colspan="3" class="px-5 py-6 text-center text-slate-400">Belum ada rombel.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        {{-- Statistik login --}}
        <div class="bg-white rounded-xl shadow-sm border border-slate-200 overflow-hidden">
            <div class="px-5 py-4 border-b border-slate-200 font-semibold text-slate-700">Statistik Login Terakhir</div>
            <table class="w-full text-sm">
                <thead class="bg-slate-50 text-slate-500 text-xs uppercase">
                    <tr>
                        <th class="text-left px-5 py-2.5">Pengguna</th>
                        <th class="text-left px-5 py-2.5">Waktu</th>
                        <th class="text-left px-5 py-2.5">IP</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($recentLogins as $log)
                        <tr class="border-t border-slate-100">
                            <td class="px-5 py-2.5 font-medium">{{ $log->user?->name ?? '-' }}</td>
                            <td class="px-5 py-2.5 text-slate-500">{{ $log->logged_in_at?->format('d/m/Y H:i') }}</td>
                            <td class="px-5 py-2.5 text-slate-500 font-mono text-xs">{{ $log->ip_address }}</td>
                        </tr>
                    @empty
                        <tr><td colspan="3" class="px-5 py-6 text-center text-slate-400">Belum ada aktivitas login.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
@endsection
