<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login — Sistem Anggaran PH</title>
    <script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="bg-gray-50 min-h-screen flex items-center justify-center">

<div class="w-full max-w-sm">
    {{-- Logo / Judul --}}
    <div class="text-center mb-8">
        <div class="inline-flex items-center justify-center w-12 h-12 bg-amber-100 rounded-xl mb-3">
            <svg class="w-6 h-6 text-amber-700" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
            </svg>
        </div>
        <h1 class="text-lg font-semibold text-gray-800">Sistem Manajemen Anggaran</h1>
        <p class="text-sm text-gray-500 mt-1">Production House</p>
    </div>

    {{-- Card Login --}}
    <div class="bg-white border border-gray-200 rounded-2xl p-8">
        <h2 class="text-sm font-medium text-gray-700 mb-6">Masuk ke akun Anda</h2>

        @if($errors->any())
            <div class="mb-4 bg-red-50 border border-red-200 rounded-lg px-4 py-3 text-sm text-red-700">
                {{ $errors->first() }}
            </div>
        @endif

        <form method="POST" action="{{ route('login.post') }}">
            @csrf

            <div class="mb-4">
                <label class="block text-xs text-gray-500 mb-1.5">Email</label>
                <input
                    type="email"
                    name="email"
                    value="{{ old('email') }}"
                    required
                    autofocus
                    placeholder="admin@ph.com"
                    class="w-full text-sm border border-gray-200 rounded-lg px-3 py-2.5 focus:outline-none focus:border-amber-400 focus:ring-1 focus:ring-amber-400"
                >
            </div>

            <div class="mb-6">
                <label class="block text-xs text-gray-500 mb-1.5">Password</label>
                <input
                    type="password"
                    name="password"
                    required
                    placeholder="••••••••"
                    class="w-full text-sm border border-gray-200 rounded-lg px-3 py-2.5 focus:outline-none focus:border-amber-400 focus:ring-1 focus:ring-amber-400"
                >
            </div>

            <div class="flex items-center justify-between mb-6">
                <label class="flex items-center gap-2 text-xs text-gray-500 cursor-pointer">
                    <input type="checkbox" name="remember" class="rounded border-gray-300 text-amber-600">
                    Ingat saya
                </label>
            </div>

            <button
                type="submit"
                class="w-full bg-amber-600 hover:bg-amber-700 text-white text-sm font-medium rounded-lg px-4 py-2.5 transition-colors"
            >
                Masuk
            </button>
        </form>
    </div>

    {{-- Info akun dummy --}}
    <div class="mt-4 bg-blue-50 border border-blue-200 rounded-xl p-4 text-xs text-blue-800">
        <p class="font-medium mb-2">Akun untuk testing:</p>
        <div class="space-y-1">
            <p><span class="font-medium">Admin</span> → admin@ph.com</p>
            <p><span class="font-medium">Produser</span> → dewi@ph.com</p>
            <p><span class="font-medium">Crew</span> → reza@ph.com</p>
            <p class="mt-2 text-blue-600">Password semua: <span class="font-mono font-medium">password</span></p>
        </div>
    </div>
</div>

</body>
</html>
