@extends('layouts.app')

@section('title', $objective->exists ? 'Ubah Tujuan Pembelajaran' : 'Tambah Tujuan Pembelajaran')

@section('content')
    <div class="max-w-3xl bg-white rounded-xl shadow-sm border border-slate-200 p-6">
        <form method="POST"
              action="{{ $objective->exists ? route('learning-objectives.update', $objective) : route('learning-objectives.store') }}">
            @csrf
            @if($objective->exists) @method('PUT') @endif

            <input type="hidden" name="subject_id" value="{{ old('subject_id', $objective->subject_id) }}">
            <input type="hidden" name="classroom_id" value="{{ old('classroom_id', $objective->classroom_id) }}">
            <input type="hidden" name="tahun_ajaran" value="{{ old('tahun_ajaran', $objective->academic_year) }}">
            <input type="hidden" name="semester" value="{{ old('semester', $objective->semester) }}">

            <div class="grid md:grid-cols-2 gap-4 mb-4 rounded-lg bg-slate-50 border border-slate-200 p-4 text-sm">
                <div>
                    <span class="text-slate-500">Kelas:</span>
                    <span class="font-semibold ml-1">
                        {{ \App\Models\Classroom::find(old('classroom_id', $objective->classroom_id))?->name ?? '-' }}
                    </span>
                </div>
                <div>
                    <span class="text-slate-500">Kelas:</span>
                    <span class="font-semibold ml-1">
                        {{ $objective->classroom?->name ?? '-' }}
                    </span>
                </div>
                <div>
                    <span class="text-slate-500">Tahun Ajaran:</span>
                    <span class="font-semibold ml-1">{{ old('tahun_ajaran', $objective->academic_year) }}</span>
                </div>
                <div>
                    <span class="text-slate-500">Semester:</span>
                    <span class="font-semibold ml-1">
                        {{ old('semester', $objective->semester) === '2' ? '2 (Genap)' : '1 (Ganjil)' }}
                    </span>
                </div>
            </div>

            <div class="space-y-4">
                <div class="grid md:grid-cols-2 gap-4">
                    <div>
                        <label class="block text-sm font-medium text-slate-600 mb-1">Kode TP <span class="text-rose-500">*</span></label>
                        <input name="code" value="{{ old('code', $objective->code) }}" required placeholder="TP-1.1"
                               class="w-full rounded-lg border border-slate-300 px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-indigo-500">
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-slate-600 mb-1">Urutan</label>
                        <input type="number" name="order_no" min="0" value="{{ old('order_no', $objective->order_no ?? 0) }}"
                               class="w-full rounded-lg border border-slate-300 px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-indigo-500">
                    </div>
                </div>

                <div>
                    <label class="block text-sm font-medium text-slate-600 mb-1">
                        Deskripsi Tujuan Pembelajaran / Capaian <span class="text-rose-500">*</span>
                    </label>
                    <textarea name="description" rows="4" required
                              placeholder="Tuliskan capaian pembelajaran sesuai prinsip Kurikulum Merdeka..."
                              class="w-full rounded-lg border border-slate-300 px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-indigo-500">{{ old('description', $objective->description) }}</textarea>
                </div>

                <div>
                    <label class="block text-sm font-medium text-slate-600 mb-1">
                        Pendekatan Pemahaman Bermakna / <i>Deep Learning</i>
                    </label>
                    <textarea name="approach" rows="3"
                              placeholder="Misal: projek P5, diskusi, praktik langsung, inquiry, dst."
                              class="w-full rounded-lg border border-slate-300 px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-indigo-500">{{ old('approach', $objective->approach) }}</textarea>
                    <p class="text-xs text-slate-400 mt-1">Deskripsi TP tertinggi &amp; terendah menjadi Capaian Kompetensi otomatis di rapor.</p>
                </div>
            </div>

            <div class="mt-6 flex gap-3">
                <button class="rounded-lg bg-indigo-600 hover:bg-indigo-700 text-white px-5 py-2 text-sm font-medium">Simpan</button>
                <a href="{{ route('learning-objectives.index') }}" class="rounded-lg bg-slate-200 hover:bg-slate-300 px-5 py-2 text-sm font-medium">Batal</a>
            </div>
        </form>
    </div>
@endsection
