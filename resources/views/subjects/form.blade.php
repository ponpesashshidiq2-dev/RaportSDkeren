@extends('layouts.app')

@section('title', $subject->exists ? 'Ubah Mata Pelajaran' : 'Tambah Mata Pelajaran')

@section('content')
    <div class="max-w-xl bg-white rounded-xl shadow-sm border border-slate-200 p-6">
        <form method="POST"
              action="{{ $subject->exists ? route('subjects.update', $subject) : route('subjects.store') }}">
            @csrf
            @if($subject->exists) @method('PUT') @endif

            <div class="space-y-4">
                <div class="grid grid-cols-2 gap-4">
                    <div>
                        <label class="block text-sm font-medium text-slate-600 mb-1">Kode</label>
                        <input name="code" value="{{ old('code', $subject->code) }}" maxlength="30" placeholder="NA01"
                               class="w-full rounded-lg border border-slate-300 px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-indigo-500">
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-slate-600 mb-1">Fase</label>
                        <input name="phase" value="{{ old('phase', $subject->phase) }}" maxlength="5" placeholder="A / B / C"
                               class="w-full rounded-lg border border-slate-300 px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-indigo-500">
                    </div>
                </div>

                <div>
                    <label class="block text-sm font-medium text-slate-600 mb-1">Nama Mata Pelajaran <span class="text-rose-500">*</span></label>
                    <input name="name" value="{{ old('name', $subject->name) }}" required
                           class="w-full rounded-lg border border-slate-300 px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-indigo-500">
                </div>

                <div>
                    <label class="block text-sm font-medium text-slate-600 mb-1">Muatan <span class="text-rose-500">*</span></label>
                    <select name="muatan" required
                            class="w-full rounded-lg border border-slate-300 px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-indigo-500">
                        @foreach(['agama' => 'Muatan Agama', 'nasional' => 'Muatan Nasional', 'lokal' => 'Muatan Lokal'] as $value => $label)
                            <option value="{{ $value }}" {{ old('muatan', $subject->muatan) === $value ? 'selected' : '' }}>{{ $label }}</option>
                        @endforeach
                    </select>
                </div>

                <div>
                    <label class="block text-sm font-medium text-slate-600 mb-1">KKM</label>
                    <input type="number" name="kkm" min="0" max="100" step="0.01" value="{{ old('kkm', $subject->kkm) }}"
                           placeholder="Kosongkan jika mapel tidak dicetak di rapor"
                           class="w-full rounded-lg border border-slate-300 px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-indigo-500">
                    <p class="text-xs text-slate-400 mt-1">
                        KKM diisi &rarr; mapel <b>muncul &amp; dicetak</b> di rapor.
                        KKM kosong &rarr; mapel <b>disembunyikan</b> dari rapor.
                    </p>
                </div>
            </div>

            <div class="mt-6 flex gap-3">
                <button class="rounded-lg bg-indigo-600 hover:bg-indigo-700 text-white px-5 py-2 text-sm font-medium">Simpan</button>
                <a href="{{ route('subjects.index') }}" class="rounded-lg bg-slate-200 hover:bg-slate-300 px-5 py-2 text-sm font-medium">Batal</a>
            </div>
        </form>
    </div>
@endsection
