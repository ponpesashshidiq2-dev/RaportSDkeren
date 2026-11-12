@extends('layouts.app')

@section('title', $student->exists ? 'Ubah Siswa' : 'Tambah Siswa')

@section('content')
    <div class="max-w-3xl bg-white rounded-xl shadow-sm border border-slate-200 p-6">
        <form method="POST"
              action="{{ $student->exists ? route('students.update', $student) : route('students.store') }}">
            @csrf
            @if($student->exists) @method('PUT') @endif

            <div class="grid md:grid-cols-2 gap-4">
                <div>
                    <label class="block text-sm font-medium text-slate-600 mb-1">NISN</label>
                    <input name="nisn" value="{{ old('nisn', $student->nisn) }}" maxlength="20"
                           class="w-full rounded-lg border border-slate-300 px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-indigo-500">
                </div>
                <div>
                    <label class="block text-sm font-medium text-slate-600 mb-1">NIS</label>
                    <input name="nis" value="{{ old('nis', $student->nis) }}" maxlength="30"
                           class="w-full rounded-lg border border-slate-300 px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-indigo-500">
                </div>

                <div class="md:col-span-2">
                    <label class="block text-sm font-medium text-slate-600 mb-1">Nama Peserta Didik <span class="text-rose-500">*</span></label>
                    <input name="nama" value="{{ old('nama', $student->nama) }}" required
                           class="w-full rounded-lg border border-slate-300 px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-indigo-500">
                </div>

                <div>
                    <label class="block text-sm font-medium text-slate-600 mb-1">Jenis Kelamin <span class="text-rose-500">*</span></label>
                    <select name="jenis_kelamin" required
                            class="w-full rounded-lg border border-slate-300 px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-indigo-500">
                        <option value="L" {{ old('jenis_kelamin', $student->jenis_kelamin) === 'L' ? 'selected' : '' }}>Laki-laki</option>
                        <option value="P" {{ old('jenis_kelamin', $student->jenis_kelamin) === 'P' ? 'selected' : '' }}>Perempuan</option>
                    </select>
                </div>

                <div>
                    <label class="block text-sm font-medium text-slate-600 mb-1">Kelas <span class="text-rose-500">*</span></label>
                    <select name="classroom_id" required
                            class="w-full rounded-lg border border-slate-300 px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-indigo-500">
                        <option value="">-- Pilih --</option>
                        @foreach($classrooms as $rombel)
                            <option value="{{ $rombel->id }}" {{ old('classroom_id', $student->classroom_id) == $rombel->id ? 'selected' : '' }}>
                                {{ $rombel->name }} (TA {{ $rombel->academic_year }})
                            </option>
                        @endforeach
                    </select>
                </div>

                <div>
                    <label class="block text-sm font-medium text-slate-600 mb-1">Tempat Lahir <span class="text-rose-500">*</span></label>
                    <input name="tempat_lahir" value="{{ old('tempat_lahir', $student->tempat_lahir) }}" required
                           class="w-full rounded-lg border border-slate-300 px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-indigo-500">
                </div>

                <div>
                    <label class="block text-sm font-medium text-slate-600 mb-1">Tanggal Lahir <span class="text-rose-500">*</span></label>
                    <input type="date" name="tanggal_lahir" value="{{ old('tanggal_lahir', $student->tanggal_lahir?->format('Y-m-d')) }}" required
                           class="w-full rounded-lg border border-slate-300 px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-indigo-500">
                </div>

                <div class="md:col-span-2">
                    <label class="block text-sm font-medium text-slate-600 mb-1">Alamat <span class="text-rose-500">*</span></label>
                    <textarea name="alamat" rows="2" required
                              class="w-full rounded-lg border border-slate-300 px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-indigo-500">{{ old('alamat', $student->alamat) }}</textarea>
                </div>

                <div>
                    <label class="block text-sm font-medium text-slate-600 mb-1">No. HP</label>
                    <input name="no_hp" value="{{ old('no_hp', $student->no_hp) }}" maxlength="20"
                           class="w-full rounded-lg border border-slate-300 px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-indigo-500">
                </div>

                <div>
                    <label class="block text-sm font-medium text-slate-600 mb-1">Nama Ayah</label>
                    <input name="nama_ayah" value="{{ old('nama_ayah', $student->nama_ayah) }}"
                           class="w-full rounded-lg border border-slate-300 px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-indigo-500">
                </div>

                <div>
                    <label class="block text-sm font-medium text-slate-600 mb-1">Nama Ibu</label>
                    <input name="nama_ibu" value="{{ old('nama_ibu', $student->nama_ibu) }}"
                           class="w-full rounded-lg border border-slate-300 px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-indigo-500">
                </div>

                <div>
                    <label class="block text-sm font-medium text-slate-600 mb-1">Orang Tua/Wali Murid <span class="text-slate-400 text-xs">(tanda tangan rapor)</span></label>
                    <input name="nama_wali" value="{{ old('nama_wali', $student->nama_wali) }}"
                           placeholder="Kosongkan = otomatis pakai nama ayah/ibu"
                           class="w-full rounded-lg border border-slate-300 px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-indigo-500">
                </div>

                <div class="md:col-span-2 flex items-center gap-2">
                    <input type="hidden" name="is_active" value="0">
                    <input type="checkbox" id="is_active" name="is_active" value="1"
                           {{ old('is_active', $student->is_active ?? true) ? 'checked' : '' }}
                           class="rounded border-slate-300">
                    <label for="is_active" class="text-sm text-slate-600">Siswa aktif</label>
                </div>
            </div>

            <div class="mt-6 flex gap-3">
                <button type="submit" class="rounded-lg bg-indigo-600 hover:bg-indigo-700 text-white px-5 py-2 text-sm font-medium">
                    Simpan
                </button>
                <a href="{{ route('students.index') }}" class="rounded-lg bg-slate-200 hover:bg-slate-300 px-5 py-2 text-sm font-medium">
                    Batal
                </a>
            </div>
        </form>
    </div>
@endsection
