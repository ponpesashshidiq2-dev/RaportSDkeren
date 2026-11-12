@extends('layouts.app')

@section('title', 'Mata Pelajaran & KKM')

@section('content')
    <div class="flex items-center justify-between mb-5">
        <p class="text-sm text-slate-500">
            Mapel dengan <span class="font-semibold">KKM terisi</span> akan otomatis dicetak di rapor.
            Mapel tanpa KKM disembunyikan dari rapor.
        </p>
        <a href="{{ route('subjects.create') }}"
           class="rounded-lg bg-indigo-600 hover:bg-indigo-700 text-white px-4 py-2 text-sm font-medium">
            + Tambah Mapel
        </a>
    </div>

    @php
        $muatanLabels = [
            'agama' => ['title' => 'Muatan Agama', 'badge' => 'bg-emerald-100 text-emerald-700'],
            'nasional' => ['title' => 'Muatan Nasional', 'badge' => 'bg-sky-100 text-sky-700'],
            'lokal' => ['title' => 'Muatan Lokal', 'badge' => 'bg-violet-100 text-violet-700'],
        ];
    @endphp

    <div class="space-y-8">
        @foreach(['agama', 'nasional', 'lokal'] as $muatan)
            @php $meta = $muatanLabels[$muatan]; @endphp
            <div class="bg-white rounded-xl shadow-sm border border-slate-200 overflow-hidden">
                <div class="px-5 py-3.5 border-b border-slate-200 flex items-center gap-3">
                    <span class="text-xs font-bold uppercase tracking-wide {{ $meta['badge'] }} rounded-full px-3 py-1">
                        {{ $meta['title'] }}
                    </span>
                    <span class="text-xs text-slate-400">{{ $subjectsByMuatan->get($muatan, collect())->count() }} mapel</span>
                </div>

                <table class="w-full text-sm">
                    <thead class="bg-slate-50 text-slate-500 text-xs uppercase">
                        <tr>
                            <th class="text-left px-5 py-2.5">Kode</th>
                            <th class="text-left px-5 py-2.5">Mata Pelajaran</th>
                            <th class="text-left px-5 py-2.5">KKM</th>
                            <th class="text-left px-5 py-2.5">Status Rapor</th>
                            <th class="text-right px-5 py-2.5">Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($subjectsByMuatan->get($muatan, collect()) as $subject)
                            <tr class="border-t border-slate-100 hover:bg-slate-50">
                                <td class="px-5 py-3 font-mono text-xs text-slate-500">{{ $subject->code ?? '-' }}</td>
                                <td class="px-5 py-3 font-medium text-slate-800">{{ $subject->name }}</td>
                                <td class="px-5 py-3">
                                    @if($subject->kkm !== null)
                                        <span class="font-bold text-indigo-700">{{ rtrim(rtrim(number_format($subject->kkm, 2), '0'), '.') }}</span>
                                    @else
                                        <span class="text-slate-300">-</span>
                                    @endif
                                </td>
                                <td class="px-5 py-3">
                                    @if($subject->kkm !== null)
                                        <span class="text-xs rounded-full bg-emerald-100 text-emerald-700 px-2.5 py-1 font-semibold">Dicetak di rapor</span>
                                    @else
                                        <span class="text-xs rounded-full bg-slate-100 text-slate-500 px-2.5 py-1 font-semibold">Disembunyikan</span>
                                    @endif
                                </td>
                                <td class="px-5 py-3 text-right whitespace-nowrap">
                                    <a href="{{ route('subjects.edit', $subject) }}" class="text-indigo-600 hover:underline text-xs font-semibold">Ubah KKM / Data</a>
                                    <form action="{{ route('subjects.destroy', $subject) }}" method="POST" class="inline"
                                          onsubmit="return confirm('Hapus mapel {{ $subject->name }}?')">
                                        @csrf @method('DELETE')
                                        <button class="text-rose-600 hover:underline text-xs font-semibold ml-3">Hapus</button>
                                    </form>
                                </td>
                            </tr>
                        @empty
                            <tr><td colspan="5" class="px-5 py-6 text-center text-slate-400">Belum ada mapel pada muatan ini.</td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        @endforeach
    </div>
@endsection
