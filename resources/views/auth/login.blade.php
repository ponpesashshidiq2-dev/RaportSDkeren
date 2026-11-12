<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Masuk - {{ config('app.name', 'E-Raport SD') }}</title>
    <script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="bg-indigo-900 min-h-screen flex items-center justify-center p-4">
<div class="w-full max-w-md">
    <div class="text-center text-indigo-100 mb-6">
        <div class="text-2xl font-bold text-white">E-Raport SD</div>
        <div class="text-sm">Sistem Informasi Rapor Kurikulum Merdeka</div>
    </div>

    <div class="bg-white rounded-xl shadow-xl p-8">
        <h1 class="text-lg font-semibold text-slate-700 mb-1">Masuk ke Aplikasi</h1>
        <p class="text-sm text-slate-500 mb-6">Silakan masuk dengan akun Anda.</p>

        @if($errors->any())
            <div class="mb-4 rounded-lg bg-rose-50 border border-rose-200 px-4 py-3 text-sm text-rose-700">
                {{ $errors->first() }}
            </div>
        @endif

        <form method="POST" action="{{ route('login.attempt') }}" class="space-y-4">
            @csrf

            <div>
                <label for="email" class="block text-sm font-medium text-slate-600 mb-1">Email</label>
                <input id="email" name="email" type="email" value="{{ old('email') }}" required autofocus
                       class="w-full rounded-lg border border-slate-300 px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-indigo-500">
            </div>

            <div>
                <label for="password" class="block text-sm font-medium text-slate-600 mb-1">Password</label>
                <input id="password" name="password" type="password" required
                       class="w-full rounded-lg border border-slate-300 px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-indigo-500">
            </div>

            <label class="flex items-center gap-2 text-sm text-slate-600">
                <input type="checkbox" name="remember" value="1" class="rounded border-slate-300">
                Ingat saya
            </label>

            <button type="submit"
                    class="w-full rounded-lg bg-indigo-600 hover:bg-indigo-700 text-white font-semibold py-2.5 text-sm">
                Masuk
            </button>
        </form>
    </div>

    <p class="text-center text-xs text-indigo-300 mt-6">
        Akun demo: admin@sekolah.test / password
    </p>
</div>
</body>
</html>
