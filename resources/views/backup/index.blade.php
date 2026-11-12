@extends('layouts.app')

@section('title', 'Backup & Restore Database')

@section('content')
    <div class="grid lg:grid-cols-2 gap-6">
        {{-- Backup --}}
        <div class="bg-white rounded-xl shadow-sm border border-slate-200 p-6">
            <h2 class="font-semibold text-slate-700 mb-1">Backup Database (SQL Dump)</h2>
            <p class="text-sm text-slate-500 mb-4">
                Mengunduh seluruh database aplikasi ke file <code class="bg-slate-100 px-1 rounded">.sql</code>
                menggunakan <code class="bg-slate-100 px-1 rounded">mysqldump</code>.
            </p>

            @unless($driverReady)
                <div class="mb-4 rounded-lg bg-amber-50 border border-amber-200 px-4 py-3 text-xs text-amber-800">
                    Binary <b>mysqldump</b> tidak ditemukan. Tambahkan path-nya ke environment variable
                    <code>BACKUP_MYSQLDUMP_PATH</code> (misal:
                    <code>C:\xampp\mysql\bin\mysqldump.exe</code>).
                </div>
            @endunless

            <form method="POST" action="{{ route('backup.download') }}">
                @csrf
                <button class="rounded-lg bg-indigo-600 hover:bg-indigo-700 text-white px-5 py-2.5 text-sm font-medium">
                    Unduh Backup Sekarang
                </button>
            </form>

            <div class="mt-6">
                <h3 class="text-sm font-semibold text-slate-600 mb-2">File Backup Tersedia</h3>
                <ul class="divide-y divide-slate-100 border border-slate-200 rounded-lg">
                    @forelse($files as $file)
                        <li class="px-4 py-2.5 flex items-center justify-between text-sm">
                            <span class="font-mono text-xs">{{ $file->getFilename() }}</span>
                            <span class="flex items-center gap-3">
                                <span class="text-xs text-slate-400">{{ number_format($file->getSize() / 1024, 1) }} KB</span>
                                <form method="POST" action="{{ route('backup.destroy', $file->getFilename()) }}"
                                      onsubmit="return confirm('Hapus file backup ini?')">
                                    @csrf @method('DELETE')
                                    <button class="text-rose-600 hover:underline text-xs font-semibold">Hapus</button>
                                </form>
                            </span>
                        </li>
                    @empty
                        <li class="px-4 py-5 text-center text-sm text-slate-400">Belum ada file backup.</li>
                    @endforelse
                </ul>
            </div>
        </div>

        {{-- Restore --}}
        <div class="bg-white rounded-xl shadow-sm border border-slate-200 p-6 h-fit">
            <h2 class="font-semibold text-slate-700 mb-1">Restore Database</h2>
            <p class="text-sm text-slate-500 mb-4">
                Mengunggah file <code class="bg-slate-100 px-1 rounded">.sql</code> untuk memulihkan data.
            </p>

            <div class="mb-4 rounded-lg bg-rose-50 border border-rose-200 px-4 py-3 text-xs text-rose-800">
                <b>Peringatan:</b> restore akan menimpa/menambahkan data sesuai isi file backup.
                Pastikan file berasal dari aplikasi ini.
            </div>

            <form method="POST" action="{{ route('backup.restore') }}" enctype="multipart/form-data" class="space-y-4">
                @csrf
                <div>
                    <label class="block text-sm font-medium text-slate-600 mb-1">File Backup (.sql) <span class="text-rose-500">*</span></label>
                    <input type="file" name="backup_file" accept=".sql,.txt" required
                           class="w-full text-sm border border-slate-300 rounded-lg px-3 py-2 file:mr-3 file:rounded-md file:border-0 file:bg-indigo-50 file:text-indigo-700 file:px-3 file:py-1.5 file:text-sm file:font-medium">
                </div>
                <button class="rounded-lg bg-rose-600 hover:bg-rose-700 text-white px-5 py-2.5 text-sm font-medium">
                    Restore Sekarang
                </button>
            </form>
        </div>
    </div>
@endsection
