@extends('layouts.app')

@section('title', 'Import Data Siswa (Excel)')

@section('content')
    <div class="grid lg:grid-cols-3 gap-6">
        <div class="lg:col-span-2 bg-white rounded-xl shadow-sm border border-slate-200 p-6">
            <h2 class="font-semibold text-slate-700 mb-1">Upload File Excel / CSV</h2>
            <p class="text-sm text-slate-500 mb-5">
                Format file: <code class="bg-slate-100 px-1 rounded">.xlsx</code>,
                <code class="bg-slate-100 px-1 rounded">.xls</code>, atau
                <code class="bg-slate-100 px-1 rounded">.csv</code> (maks 5 MB).
            </p>

            <form method="POST" action="{{ route('students.import.store') }}" enctype="multipart/form-data" class="space-y-4">
                @csrf

                <div>
                    <label class="block text-sm font-medium text-slate-600 mb-1">File <span class="text-rose-500">*</span></label>
                    <input type="file" name="file" accept=".xlsx,.xls,.csv" required
                           class="w-full text-sm border border-slate-300 rounded-lg px-3 py-2 file:mr-3 file:rounded-md file:border-0 file:bg-indigo-50 file:text-indigo-700 file:px-3 file:py-1.5 file:text-sm file:font-medium">
                </div>

                <div>
                    <label class="block text-sm font-medium text-slate-600 mb-1">Kelas Default</label>
                    <select name="kelas_id"
                            class="w-full rounded-lg border border-slate-300 px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-indigo-500">
                        <option value="">-- Tanpa kelas (wajib ada kolom Kelas di file) --</option>
                        @foreach($classrooms as $rombel)
                            <option value="{{ $rombel->id }}">{{ $rombel->name }} (TA {{ $rombel->academic_year }})</option>
                        @endforeach
                    </select>
                    <p class="text-xs text-slate-400 mt-1">Dipakai jika kolom "Kelas" pada file kosong.</p>
                </div>

                <button class="rounded-lg bg-indigo-600 hover:bg-indigo-700 text-white px-5 py-2.5 text-sm font-medium">
                    Import Sekarang
                </button>
            </form>
        </div>

        <div class="bg-white rounded-xl shadow-sm border border-slate-200 p-6">
            <h3 class="font-semibold text-slate-700 mb-3">Kolom Wajib (Baris 1)</h3>
            <table class="w-full text-xs">
                <thead class="text-slate-400 uppercase text-[10px]">
                    <tr><th class="text-left pb-2">Header</th><th class="text-left pb-2">Keterangan</th></tr>
                </thead>
                <tbody class="divide-y divide-slate-100">
                    <tr><td class="py-2 font-mono">NISN</td><td class="text-slate-500">Unik, boleh kosong</td></tr>
                    <tr><td class="py-2 font-mono">NIS</td><td class="text-slate-500">Nomor induk</td></tr>
                    <tr><td class="py-2 font-mono">Nama</td><td class="text-slate-500 font-semibold">Wajib</td></tr>
                    <tr><td class="py-2 font-mono">Jenis Kelamin</td><td class="text-slate-500">L / Laki-laki / P</td></tr>
                    <tr><td class="py-2 font-mono">Tempat Lahir</td><td class="text-slate-500 font-semibold">Wajib</td></tr>
                    <tr><td class="py-2 font-mono">Tanggal Lahir</td><td class="text-slate-500">dd/mm/yyyy atau yyyy-mm-dd</td></tr>
                    <tr><td class="py-2 font-mono">Alamat</td><td class="text-slate-500 font-semibold">Wajib</td></tr>
                    <tr><td class="py-2 font-mono">No HP</td><td class="text-slate-500">Opsional</td></tr>
                    <tr><td class="py-2 font-mono">Nama Ayah</td><td class="text-slate-500">Opsional</td></tr>
                    <tr><td class="py-2 font-mono">Nama Ibu</td><td class="text-slate-500">Opsional</td></tr>
                    <tr><td class="py-2 font-mono">Kelas</td><td class="text-slate-500">Nama rombel, mis. 1A</td></tr>
                </tbody>
            </table>

            <div class="mt-4 rounded-lg bg-amber-50 border border-amber-200 p-3 text-xs text-amber-800">
                Nama kelas pada file harus sama persis dengan data rombel yang sudah dibuat.
            </div>
        </div>
    </div>
@endsection
