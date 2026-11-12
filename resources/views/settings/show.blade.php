@extends('layouts.app')

@section('title', 'Pengaturan')

@section('content')
    <div class="max-w-3xl space-y-5">
        {{-- Info akun --}}
        <div class="bg-white rounded-xl shadow-sm border border-slate-200 p-5">
            <h2 class="font-semibold text-slate-700 mb-1">Akun</h2>
            <dl class="text-sm space-y-2">
                <div class="flex justify-between border-b border-slate-100 pb-2">
                    <dt class="text-slate-500">Nama</dt>
                    <dd class="font-medium">{{ $user->name }}</dd>
                </div>
                <div class="flex justify-between border-b border-slate-100 pb-2">
                    <dt class="text-slate-500">Email</dt>
                    <dd class="font-medium">{{ $user->email }}</dd>
                </div>
                <div class="flex justify-between border-b border-slate-100 pb-2">
                    <dt class="text-slate-500">Role</dt>
                    <dd class="font-medium">{{ $user->roleLabel() }}</dd>
                </div>
            </dl>
        </div>

        @if($user->isStaff())
            {{-- Pengaturan kelas aktif --}}
            <div class="bg-white rounded-xl shadow-sm border border-slate-200 p-5">
                <h2 class="font-semibold text-slate-700 mb-1">Kelas Aktif Saya</h2>
                <p class="text-xs text-slate-500 mb-4">
                    Kelas hanya diatur <b>di halaman ini</b>. Halaman Nilai, CP/TP, Data Siswa,
                    dan Rapor akan otomatis memakai kelas yang Anda pilih — tidak ada pemilihan
                    kelas di halaman-halaman tersebut.
                </p>

                <form method="POST" action="{{ route('settings.update') }}" class="flex flex-wrap items-end gap-3">
                    @csrf
                    <div class="min-w-[220px]">
                        <label class="block text-xs text-slate-500 mb-1">Pilih Kelas <span class="text-rose-500">*</span></label>
                        <select name="classroom_id" required
                                class="w-full rounded-lg border border-slate-300 px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-indigo-500">
                            <option value="">-- Pilih kelas --</option>
                            @foreach($classrooms as $rombel)
                                <option value="{{ $rombel->id }}" {{ old('classroom_id', $user->classroom_id) == $rombel->id ? 'selected' : '' }}>
                                    Kelas {{ $rombel->name }} (TA {{ $rombel->academic_year }})
                                </option>
                            @endforeach
                        </select>
                    </div>
                    <button class="rounded-lg bg-indigo-600 hover:bg-indigo-700 text-white px-5 py-2 text-sm font-medium">
                        Simpan Pengaturan
                    </button>
                </form>

                @unless($user->classroom_id)
                    <div class="mt-4 rounded-lg bg-amber-50 border border-amber-200 px-4 py-3 text-sm text-amber-800">
                        Anda belum menetapkan kelas. Halaman Nilai, CP/TP, dan Rapor akan
                        mengarahkan Anda ke halaman ini sampai kelas dipilih.
                    </div>
                @endunless
            </div>
        @else
            {{-- Siswa/Orang Tua: kelas mengikuti data siswa --}}
            <div class="bg-white rounded-xl shadow-sm border border-slate-200 p-5">
                <h2 class="font-semibold text-slate-700 mb-3">Kelas Saya</h2>
                @if($user->student)
                    <div class="text-sm">
                        <span class="text-slate-500">Kelas:</span>
                        <span class="font-semibold text-slate-800">{{ $user->student->classroom?->name ?? '-' }}</span>
                        <span class="text-slate-400">(mengikuti data siswa yang tertaut)</span>
                    </div>
                @else
                    <p class="text-sm text-slate-400">Akun belum tertaut ke data siswa.</p>
                @endif
            </div>
        @endif
    </div>
@endsection
