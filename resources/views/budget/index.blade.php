@extends('layouts.app')
@section('title', 'Dashboard Anggaran')

@section('content')
<!-- Header dengan tombol Hire Crew -->
<div class="mb-6 flex items-center justify-between">
    <div>
        <h1 class="text-2xl font-bold text-gray-900">Dashboard Anggaran</h1>
        <p class="text-sm text-gray-500 mt-1">Monitoring keuangan seluruh project — May 2026</p>
    </div>
    
    <!-- Tombol Hire Crew (Admin & Produser Only) -->
    @if(auth()->user()->isAdmin() || auth()->user()->isProduser())
    <a href="{{ route('budget.hire-crew-page') }}" class="px-4 py-2 bg-amber-600 text-white rounded-lg hover:bg-amber-700 font-medium text-sm transition">
        + Input Pengeluaran
    </a>
    @endif
</div>

<!-- Rest of existing dashboard content -->
<div class="grid grid-cols-4 gap-4 mb-6">
    <!-- Total Anggaran Card -->
    <div class="bg-white border border-gray-200 rounded-xl p-5">
        <p class="text-xs text-gray-500 font-medium mb-2">Total anggaran aktif</p>
        <p class="text-2xl font-bold text-gray-900">Rp {{ number_format($totalAnggaran / 1000000000, 1) }}t</p>
        <p class="text-xs text-gray-400 mt-1">{{ count($projects) }} project berjalan</p>
    </div>

    <!-- Total Terpakai Card -->
    <div class="bg-white border border-gray-200 rounded-xl p-5">
        <p class="text-xs text-gray-500 font-medium mb-2">Total terpakai</p>
        <p class="text-2xl font-bold text-red-600">Rp {{ number_format($totalTerpakai / 1000000000, 1) }}t</p>
        <p class="text-xs text-red-500 mt-1">{{ round(($totalTerpakai / $totalAnggaran) * 100) }}% dari total</p>
    </div>

    <!-- Sisa Anggaran Card -->
    <div class="bg-white border border-gray-200 rounded-xl p-5">
        <p class="text-xs text-gray-500 font-medium mb-2">Sisa anggaran</p>
        <p class="text-2xl font-bold text-green-600">Rp {{ number_format($sisaAnggaran / 1000000000, 1) }}t</p>
        <p class="text-xs text-green-500 mt-1">{{ round(($sisaAnggaran / $totalAnggaran) * 100) }}% tersedia</p>
    </div>

    <!-- Over Budget Card -->
    <div class="bg-white border border-gray-200 rounded-xl p-5">
        <p class="text-xs text-gray-500 font-medium mb-2">Over budget</p>
        <p class="text-2xl font-bold text-orange-600">{{ $overBudget }}</p>
        <p class="text-xs text-orange-500 mt-1">Semua aman</p>
    </div>
</div>

<!-- Charts Row -->
<div class="grid grid-cols-2 gap-4 mb-6">
    <!-- Budget vs Realisasi -->
    <div class="bg-white border border-gray-200 rounded-xl p-5">
        <h3 class="text-sm font-semibold text-gray-800 mb-4">Budget vs realisasi per project</h3>
        <div class="space-y-3">
            @foreach($projects as $proj)
            <div>
                <div class="flex items-center justify-between mb-1">
                    <p class="text-xs font-medium text-gray-700">{{ $proj->nama_project }}</p>
                    <p class="text-xs {{ $proj->budget_status === 'over' ? 'text-red-600' : 'text-green-600' }} font-medium">
                        {{ round(($proj->total_terpakai / $proj->budget_total) * 100) }}%
                    </p>
                </div>
                <div class="w-full bg-gray-200 rounded-full h-2">
                    <div class="bg-amber-500 h-2 rounded-full" style="width: {{ min(round(($proj->total_terpakai / $proj->budget_total) * 100), 100) }}%"></div>
                </div>
            </div>
            @endforeach
        </div>
    </div>

    <!-- Distribusi Pengeluaran -->
    <div class="bg-white border border-gray-200 rounded-xl p-5">
        <h3 class="text-sm font-semibold text-gray-800 mb-4">Distribusi pengeluaran per kategori</h3>
        <div class="space-y-3">
            @foreach($distribusiKategori as $kategori => $total)
            <div>
                <div class="flex items-center justify-between mb-1">
                    <p class="text-xs font-medium text-gray-700">{{ ucfirst(str_replace('_', ' ', $kategori)) }}</p>
                    <p class="text-xs font-bold text-gray-900">Rp {{ number_format($total / 1000000, 1) }}jt</p>
                </div>
                <div class="w-full bg-gray-200 rounded-full h-2">
                    <div class="bg-blue-500 h-2 rounded-full" style="width: {{ ($total / $totalTerpakai) * 100 }}%"></div>
                </div>
            </div>
            @endforeach
        </div>
    </div>
</div>

<!-- Notifications & Invoices Row -->
<div class="grid grid-cols-2 gap-4 mb-6">
    <!-- Notifikasi & Peringatan -->
    <div class="bg-white border border-gray-200 rounded-xl p-5">
        <h3 class="text-sm font-semibold text-gray-800 mb-4">Notifikasi & peringatan anggaran</h3>
        <div class="space-y-2">
            @forelse($notifikasi as $notif)
            <div class="flex items-start gap-3 p-3 rounded-lg bg-yellow-50 border border-yellow-200">
                <div class="flex-shrink-0 w-2 h-2 mt-1.5 rounded-full bg-yellow-500"></div>
                <div class="flex-1">
                    <p class="text-xs text-yellow-800">{{ $notif->pesan }}</p>
                    <p class="text-xs text-yellow-600 mt-0.5">{{ $notif->created_at->diffForHumans() }}</p>
                </div>
            </div>
            @empty
            <p class="text-xs text-gray-400 text-center py-4">Tidak ada notifikasi</p>
            @endforelse
        </div>
    </div>

    <!-- Invoice Belum Dibayar -->
    <div class="bg-white border border-gray-200 rounded-xl p-5">
        <h3 class="text-sm font-semibold text-gray-800 mb-4">Invoice vendor — belum dibayar</h3>
        <div class="space-y-2">
            @forelse($invoices as $inv)
            <div class="p-3 border border-gray-200 rounded-lg hover:bg-gray-50">
                <div class="flex items-start justify-between">
                    <div>
                        <p class="text-xs font-medium text-gray-800">{{ $inv->nomor_invoice }} • {{ $inv->project->nama_project }}</p>
                        <p class="text-xs text-gray-500 mt-1">{{ $inv->nama_vendor }}</p>
                    </div>
                    <p class="text-xs font-bold text-gray-900">Rp {{ number_format($inv->jumlah / 1000000, 1) }}jt</p>
                </div>
                <p class="text-xs text-red-500 font-medium mt-2">Jatuh tempo: {{ $inv->jatuh_tempo->format('d M Y') }}</p>
            </div>
            @empty
            <p class="text-xs text-gray-400 text-center py-4">Semua invoice sudah lunas</p>
            @endforelse
        </div>
    </div>
</div>

<!-- Antrian Approval -->
<div class="bg-white border border-gray-200 rounded-xl p-5">
    <h3 class="text-sm font-semibold text-gray-800 mb-4">Antrian approval pengeluaran</h3>
    <!-- Anda bisa tambahkan tabel dengan list pengeluaran pending di sini -->
    <p class="text-xs text-gray-500">Tidak ada pengeluaran menunggu approval</p>
</div>

@endsection
