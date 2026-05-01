<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@yield('title', 'Dashboard') — Sistem Anggaran PH</title>
    <script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="bg-gray-50 text-gray-800 min-h-screen">

<div class="flex min-h-screen">

    {{-- ── SIDEBAR ──────────────────────────────── --}}
    <aside class="w-56 bg-white border-r border-gray-200 flex flex-col shrink-0">
        {{-- Brand --}}
        <div class="px-5 py-5 border-b border-gray-100">
            <p class="text-xs text-gray-400 uppercase tracking-wider">Production House</p>
            <h1 class="text-sm font-semibold text-gray-800 mt-0.5">Sistem Anggaran</h1>
        </div>

        {{-- Nav --}}
        <nav class="flex-1 px-3 py-4 space-y-0.5 text-sm">
            <a href="{{ route('budget.index') }}"
               class="flex items-center gap-2.5 px-3 py-2 rounded-lg transition-colors
               {{ request()->routeIs('budget.index') ? 'bg-amber-50 text-amber-800 font-medium' : 'text-gray-600 hover:bg-gray-100' }}">
                <svg class="w-4 h-4 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2H6a2 2 0 01-2-2V6zM14 6a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2h-2a2 2 0 01-2-2V6zM4 16a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2H6a2 2 0 01-2-2v-2zM14 16a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2h-2a2 2 0 01-2-2v-2z"/></svg>
                Dashboard
            </a>
            <a href="{{ route('budget.expenses') }}"
               class="flex items-center gap-2.5 px-3 py-2 rounded-lg transition-colors
               {{ request()->routeIs('budget.expenses') ? 'bg-amber-50 text-amber-800 font-medium' : 'text-gray-600 hover:bg-gray-100' }}">
                <svg class="w-4 h-4 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"/></svg>
                Pengeluaran
            </a>
            <a href="{{ route('budget.approvals') }}"
               class="flex items-center gap-2.5 px-3 py-2 rounded-lg transition-colors
               {{ request()->routeIs('budget.approvals') ? 'bg-amber-50 text-amber-800 font-medium' : 'text-gray-600 hover:bg-gray-100' }}">
                <svg class="w-4 h-4 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                Approval
                @php $pendingCount = \App\Models\Expense::where('status','pending')->count() @endphp
                @if($pendingCount > 0)
                    <span class="ml-auto text-xs bg-amber-100 text-amber-800 rounded-full px-1.5 py-0.5 font-medium">{{ $pendingCount }}</span>
                @endif
            </a>
            <a href="{{ route('budget.invoices') }}"
               class="flex items-center gap-2.5 px-3 py-2 rounded-lg transition-colors
               {{ request()->routeIs('budget.invoices') ? 'bg-amber-50 text-amber-800 font-medium' : 'text-gray-600 hover:bg-gray-100' }}">
                <svg class="w-4 h-4 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/></svg>
                Invoice
            </a>
            <a href="{{ route('budget.laporan') }}"
               class="flex items-center gap-2.5 px-3 py-2 rounded-lg transition-colors
               {{ request()->routeIs('budget.laporan') ? 'bg-amber-50 text-amber-800 font-medium' : 'text-gray-600 hover:bg-gray-100' }}">
                <svg class="w-4 h-4 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z"/></svg>
                Laporan
            </a>
        </nav>

        {{-- User info + logout --}}
        <div class="px-4 py-4 border-t border-gray-100">
            <div class="flex items-center gap-2.5 mb-3">
                <div class="w-7 h-7 rounded-full bg-amber-100 text-amber-800 flex items-center justify-center text-xs font-semibold shrink-0">
                    {{ strtoupper(substr(Auth::user()->name, 0, 1)) }}
                </div>
                <div class="min-w-0">
                    <p class="text-xs font-medium text-gray-800 truncate">{{ Auth::user()->name }}</p>
                    <p class="text-xs text-gray-400 capitalize">{{ Auth::user()->role }}</p>
                </div>
            </div>
            <form method="POST" action="{{ route('logout') }}">
                @csrf
                <button type="submit" class="w-full text-left text-xs text-gray-500 hover:text-red-600 px-1 transition-colors">
                    Keluar
                </button>
            </form>
        </div>
    </aside>

    {{-- ── MAIN CONTENT ─────────────────────────── --}}
    <main class="flex-1 p-6 overflow-auto">
        @if(session('success'))
            <div class="mb-4 bg-green-50 border border-green-200 text-green-800 text-sm rounded-lg px-4 py-3">
                {{ session('success') }}
            </div>
        @endif
        @if($errors->any())
            <div class="mb-4 bg-red-50 border border-red-200 text-red-800 text-sm rounded-lg px-4 py-3">
                <ul class="list-disc list-inside space-y-1">
                    @foreach($errors->all() as $e)<li>{{ $e }}</li>@endforeach
                </ul>
            </div>
        @endif

        @yield('content')
    </main>
</div>

</body>
</html>
