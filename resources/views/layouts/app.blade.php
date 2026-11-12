<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@yield('title', 'Dashboard') - {{ config('app.name', 'E-Raport SD') }}</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <style>
        body { font-family: ui-sans-serif, system-ui, -apple-system, "Segoe UI", Roboto, sans-serif; }
    </style>
</head>
<body class="bg-slate-100 text-slate-800">
<div class="flex min-h-screen">

    {{-- Sidebar --}}
    <aside class="w-64 shrink-0 bg-indigo-900 text-indigo-100 flex flex-col">
        <div class="px-5 py-5 border-b border-indigo-800">
            <div class="text-lg font-bold text-white">E-Raport SD</div>
            <div class="text-xs text-indigo-300">Kurikulum Merdeka</div>
        </div>

        @php
            $role = auth()->user()->role ?? '';
            $isStaff = in_array($role, ['super_admin', 'guru', 'wali_kelas', 'kepala_sekolah'], true);
            $isAcademic = in_array($role, ['super_admin', 'guru', 'kepala_sekolah'], true);
            $isScore = $isStaff;
            $isSuper = $role === 'super_admin';
        @endphp

        <nav class="flex-1 overflow-y-auto py-4 text-sm">
            <a href="{{ route('dashboard') }}"
               class="block px-5 py-2.5 hover:bg-indigo-800 {{ request()->routeIs('dashboard') ? 'bg-indigo-800 text-white font-semibold' : '' }}">
                Dashboard Profil
            </a>
            <a href="{{ route('profile.show') }}"
               class="block px-5 py-2.5 hover:bg-indigo-800 {{ request()->routeIs('profile.show') ? 'bg-indigo-800 text-white font-semibold' : '' }}">
                Profil Saya
            </a>

            @if($role === 'siswa_orangtua')
                <div class="px-5 pt-5 pb-1 text-[11px] uppercase tracking-wider text-indigo-400">Akun Saya</div>
                <a href="{{ route('my-scores.index') }}"
                   class="block px-5 py-2.5 hover:bg-indigo-800 {{ request()->routeIs('my-scores.*') ? 'bg-indigo-800 text-white font-semibold' : '' }}">
                    Nilai Saya <span class="text-indigo-400">(hanya milik Anda)</span>
                </a>
                <a href="{{ route('reports.index') }}"
                   class="block px-5 py-2.5 hover:bg-indigo-800 {{ request()->routeIs('reports.index') || request()->routeIs('reports.show') ? 'bg-indigo-800 text-white font-semibold' : '' }}">
                    Rapor Saya
                </a>
            @endif

            @if($isStaff)
                <div class="px-5 pt-5 pb-1 text-[11px] uppercase tracking-wider text-indigo-400">Data</div>
                <a href="{{ route('students.index') }}"
                   class="block px-5 py-2.5 hover:bg-indigo-800 {{ request()->routeIs('students.*') ? 'bg-indigo-800 text-white font-semibold' : '' }}">
                    Data Siswa
                </a>
            @endif

            @if($isAcademic)
                <div class="px-5 pt-5 pb-1 text-[11px] uppercase tracking-wider text-indigo-400">Kurikulum</div>
                <a href="{{ route('subjects.index') }}"
                   class="block px-5 py-2.5 hover:bg-indigo-800 {{ request()->routeIs('subjects.*') ? 'bg-indigo-800 text-white font-semibold' : '' }}">
                    Mata Pelajaran &amp; KKM
                </a>
                <a href="{{ route('learning-objectives.index') }}"
                   class="block px-5 py-2.5 hover:bg-indigo-800 {{ request()->routeIs('learning-objectives.*') ? 'bg-indigo-800 text-white font-semibold' : '' }}">
                    Tujuan Pembelajaran
                </a>
            @endif

            @if($isScore)
                <div class="px-5 pt-5 pb-1 text-[11px] uppercase tracking-wider text-indigo-400">Nilai</div>
                <a href="{{ route('scores.formative') }}"
                   class="block px-5 py-2.5 hover:bg-indigo-800 {{ request()->routeIs('scores.formative*') ? 'bg-indigo-800 text-white font-semibold' : '' }}">
                    Nilai Formatif
                </a>
                <a href="{{ route('scores.summative') }}"
                   class="block px-5 py-2.5 hover:bg-indigo-800 {{ request()->routeIs('scores.summative*') ? 'bg-indigo-800 text-white font-semibold' : '' }}">
                    Nilai Sumatif
                </a>
                <a href="{{ route('extracurriculars.index') }}"
                   class="block px-5 py-2.5 hover:bg-indigo-800 {{ request()->routeIs('extracurriculars.*') ? 'bg-indigo-800 text-white font-semibold' : '' }}">
                    Jenis Ekstrakurikuler
                </a>
                <a href="{{ route('scores.extracurricular') }}"
                   class="block px-5 py-2.5 hover:bg-indigo-800 {{ request()->routeIs('scores.extracurricular*') ? 'bg-indigo-800 text-white font-semibold' : '' }}">
                    Nilai Ekstrakurikuler
                </a>
                <a href="{{ route('scores.attendance') }}"
                   class="block px-5 py-2.5 hover:bg-indigo-800 {{ request()->routeIs('scores.attendance*') ? 'bg-indigo-800 text-white font-semibold' : '' }}">
                    Kehadiran Siswa
                </a>
                <a href="{{ route('reports.recap') }}"
                   class="block px-5 py-2.5 hover:bg-indigo-800 {{ request()->routeIs('reports.recap') ? 'bg-indigo-800 text-white font-semibold' : '' }}">
                    Rekap Nilai <span class="text-rose-300">(merah = turun)</span>
                </a>
                <a href="{{ route('reports.index') }}"
                   class="block px-5 py-2.5 hover:bg-indigo-800 {{ request()->routeIs('reports.index') || request()->routeIs('reports.show') ? 'bg-indigo-800 text-white font-semibold' : '' }}">
                    Cetak Rapor
                </a>
            @endif

            @if($isSuper)
                <div class="px-5 pt-5 pb-1 text-[11px] uppercase tracking-wider text-indigo-400">Sistem</div>
                <a href="{{ route('users.index') }}"
                   class="block px-5 py-2.5 hover:bg-indigo-800 {{ request()->routeIs('users.*') ? 'bg-indigo-800 text-white font-semibold' : '' }}">
                    Kelola Pengguna
                </a>
                <a href="{{ route('backup.index') }}"
                   class="block px-5 py-2.5 hover:bg-indigo-800 {{ request()->routeIs('backup.*') ? 'bg-indigo-800 text-white font-semibold' : '' }}">
                    Backup &amp; Restore
                </a>
            @endif
        </nav>

        <div class="px-5 py-4 border-t border-indigo-800 text-xs">
            <div class="font-semibold text-white">{{ auth()->user()->name }}</div>
            <div class="text-indigo-300 mb-3">{{ auth()->user()->roleLabel() }}</div>
            <form method="POST" action="{{ route('logout') }}">
                @csrf
                <button type="submit" class="text-rose-300 hover:text-rose-200 hover:underline">Keluar</button>
            </form>
        </div>
    </aside>

    {{-- Konten --}}
    <div class="flex-1 min-w-0">
        <header class="bg-white border-b border-slate-200 px-8 py-4 flex items-center justify-between">
            <h1 class="text-lg font-semibold text-slate-700">@yield('title', 'Dashboard')</h1>
            <div class="text-xs text-slate-500">{{ now()->translatedFormat('d F Y') }}</div>
        </header>

        <main class="p-8">
            @if(session('success'))
                <div class="mb-4 rounded-lg bg-emerald-50 border border-emerald-200 px-4 py-3 text-sm text-emerald-800">
                    {{ session('success') }}
                </div>
            @endif

            @if(session('error'))
                <div class="mb-4 rounded-lg bg-rose-50 border border-rose-200 px-4 py-3 text-sm text-rose-800">
                    {{ session('error') }}
                </div>
            @endif

            @if($errors->any())
                <div class="mb-4 rounded-lg bg-rose-50 border border-rose-200 px-4 py-3 text-sm text-rose-800">
                    <ul class="list-disc list-inside space-y-1">
                        @foreach($errors->all() as $error)
                            <li>{{ $error }}</li>
                        @endforeach
                    </ul>
                </div>
            @endif

            @yield('content')
        </main>
    </div>
</div>
</body>
</html>
