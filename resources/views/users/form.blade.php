@extends('layouts.app')

@section('title', $user->exists ? 'Ubah Pengguna' : 'Tambah Pengguna')

@section('content')
    <div class="max-w-3xl">
        <form method="POST"
              action="{{ $user->exists ? route('users.update', $user) : route('users.store') }}"
              class="bg-white rounded-xl shadow-sm border border-slate-200 p-6 space-y-4">
            @csrf
            @if($user->exists)
                @method('PUT')
            @endif

            <div class="grid sm:grid-cols-2 gap-4">
                <div>
                    <label class="block text-xs text-slate-500 mb-1">Nama Lengkap *</label>
                    <input type="text" name="name" value="{{ old('name', $user->name) }}" required
                           class="w-full rounded-lg border border-slate-300 px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-indigo-500">
                </div>
                <div>
                    <label class="block text-xs text-slate-500 mb-1">Email *</label>
                    <input type="email" name="email" value="{{ old('email', $user->email) }}" required
                           class="w-full rounded-lg border border-slate-300 px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-indigo-500">
                </div>
                <div>
                    <label class="block text-xs text-slate-500 mb-1">
                        Password {{ $user->exists ? '(kosongkan untuk tetap)' : '*' }}
                    </label>
                    <input type="password" name="password" {{ $user->exists ? '' : 'required' }} minlength="6"
                           placeholder="{{ $user->exists ? '••••••' : 'minimal 6 karakter' }}"
                           class="w-full rounded-lg border border-slate-300 px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-indigo-500">
                </div>
                <div>
                    <label class="block text-xs text-slate-500 mb-1">No. HP</label>
                    <input type="text" name="phone" value="{{ old('phone', $user->phone) }}"
                           class="w-full rounded-lg border border-slate-300 px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-indigo-500">
                </div>
            </div>

            <div class="grid sm:grid-cols-2 gap-4">
                <div>
                    <label class="block text-xs text-slate-500 mb-1">Role *</label>
                    <select name="role" id="role-select" required
                            {{ $user->id === auth()->id() ? 'disabled' : '' }}
                            class="w-full rounded-lg border border-slate-300 px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-indigo-500">
                        @foreach($roleLabels as $key => $label)
                            <option value="{{ $key }}" {{ old('role', $user->role) === $key ? 'selected' : '' }}>
                                {{ $label }}
                            </option>
                        @endforeach
                    </select>
                    @if($user->id === auth()->id())
                        <p class="mt-1 text-[11px] text-amber-600">Role akun sendiri tidak dapat diubah agar tidak terkunci.</p>
                        {{-- select disabled tidak terkirim: kirim role asli --}}
                        <input type="hidden" name="role" value="{{ $user->role }}">
                    @endif
                </div>

                <div>
                    <label class="block text-xs text-slate-500 mb-1">Rombel (untuk Wali Kelas/Guru)</label>
                    <select name="classroom_id" class="w-full rounded-lg border border-slate-300 px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-indigo-500">
                        <option value="">— Tanpa rombel —</option>
                        @foreach($classrooms as $rombel)
                            <option value="{{ $rombel->id }}" {{ old('classroom_id', $user->classroom_id) == $rombel->id ? 'selected' : '' }}>
                                Kelas {{ $rombel->name }} ({{ $rombel->academic_year }})
                            </option>
                        @endforeach
                    </select>
                </div>
            </div>

            <div id="student-link" class="{{ old('role', $user->role) === 'siswa_orangtua' ? '' : 'hidden' }}">
                <label class="block text-xs text-slate-500 mb-1">
                    Tautkan ke Data Siswa * <span class="text-slate-400">(wajib untuk role Siswa/Orang Tua)</span>
                </label>
                <select name="student_id" class="w-full rounded-lg border border-slate-300 px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-indigo-500">
                    <option value="">— Pilih siswa —</option>
                    @foreach($students as $opt)
                        <option value="{{ $opt->id }}" {{ old('student_id', $user->student_id) == $opt->id ? 'selected' : '' }}>
                            {{ $opt->label }}{{ $opt->linked ? ' (sudah punya akun)' : '' }}
                        </option>
                    @endforeach
                </select>
                <p class="mt-1 text-[11px] text-slate-400">
                    Pengguna dengan tautan siswa hanya dapat melihat profil &amp; nilai miliknya sendiri.
                </p>
            </div>

            <div class="flex items-center gap-3 pt-2 border-t border-slate-100">
                <button type="submit"
                        class="rounded-lg bg-indigo-600 hover:bg-indigo-700 text-white px-5 py-2 text-sm font-medium">
                    {{ $user->exists ? 'Simpan Perubahan' : 'Buat Pengguna' }}
                </button>
                <a href="{{ route('users.index') }}" class="text-sm text-slate-500 hover:text-slate-700 hover:underline">
                    Batal
                </a>
            </div>
        </form>
    </div>

    <script>
        (function () {
            var select = document.getElementById('role-select');
            var box = document.getElementById('student-link');
            if (!select || !box) return;
            select.addEventListener('change', function () {
                box.classList.toggle('hidden', this.value !== 'siswa_orangtua');
            });
        })();
    </script>
@endsection
