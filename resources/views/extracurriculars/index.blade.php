@extends('layouts.app')

@section('title', 'Jenis Ekstrakurikuler')

@section('content')
    <div class="grid lg:grid-cols-3 gap-6">
        <div class="bg-white rounded-xl shadow-sm border border-slate-200 p-5 h-fit">
            <h3 class="font-semibold text-slate-700 mb-4">Tambah Kegiatan</h3>
            <form method="POST" action="{{ route('extracurriculars.store') }}" class="space-y-3">
                @csrf
                <div>
                    <label class="block text-xs text-slate-500 mb-1">Nama Kegiatan <span class="text-rose-500">*</span></label>
                    <input name="name" required value="{{ old('name') }}" placeholder="Misal: Pramuka"
                           class="w-full rounded-lg border border-slate-300 px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-indigo-500">
                </div>
                <div>
                    <label class="block text-xs text-slate-500 mb-1">Deskripsi</label>
                    <textarea name="description" rows="2" placeholder="Keterangan singkat kegiatan..."
                              class="w-full rounded-lg border border-slate-300 px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-indigo-500">{{ old('description') }}</textarea>
                </div>
                <button class="w-full rounded-lg bg-indigo-600 hover:bg-indigo-700 text-white px-4 py-2 text-sm font-medium">
                    Simpan
                </button>
            </form>
        </div>

        <div class="lg:col-span-2 space-y-4">
            @forelse($extracurriculars as $ex)
                <div class="bg-white rounded-xl shadow-sm border border-slate-200 p-4 flex flex-wrap gap-3 items-end">
                    <form method="POST" action="{{ route('extracurriculars.update', $ex) }}" class="flex-1 flex flex-wrap gap-3 items-end">
                        @csrf @method('PUT')
                        <div class="min-w-[200px]">
                            <label class="block text-xs text-slate-500 mb-1">Nama Kegiatan</label>
                            <input name="name" value="{{ old('name', $ex->name) }}" required
                                   class="w-full rounded-lg border border-slate-300 px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-indigo-500">
                        </div>
                        <div class="flex-1 min-w-[220px]">
                            <label class="block text-xs text-slate-500 mb-1">Deskripsi</label>
                            <input name="description" value="{{ old('description', $ex->description) }}"
                                   class="w-full rounded-lg border border-slate-300 px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-indigo-500">
                        </div>
                        <button class="rounded-lg bg-indigo-600 hover:bg-indigo-700 text-white px-4 py-2 text-sm font-medium">
                            Simpan
                        </button>
                    </form>

                    <form method="POST" action="{{ route('extracurriculars.destroy', $ex) }}"
                          onsubmit="return confirm('Hapus kegiatan {{ $ex->name }}?')">
                        @csrf @method('DELETE')
                        <button class="rounded-lg bg-rose-50 hover:bg-rose-100 text-rose-700 px-4 py-2 text-sm font-medium">
                            Hapus
                        </button>
                    </form>
                </div>
            @empty
                <div class="bg-white rounded-xl shadow-sm border border-slate-200 px-5 py-8 text-center text-slate-400 text-sm">
                    Belum ada kegiatan ekstrakurikuler.
                </div>
            @endforelse
        </div>
    </div>
@endsection
