@extends('layouts.app')
@section('title', 'Dashboard Anggaran')

@section('content')
<div class="mb-6 flex justify-between items-center">
    <div>
        <h2 class="text-lg font-semibold text-gray-800">Dashboard Anggaran</h2>
        <p class="text-sm text-gray-500">Monitoring keuangan seluruh project — {{ now()->isoFormat('MMMM YYYY') }}</p>
    </div>
    <a href="{{ route('budget.expenses') }}" class="text-sm border border-gray-300 rounded-lg px-4 py-2 hover:bg-gray-100">
        + Input Pengeluaran
    </a>
</div>

{{-- Metrik --}}
<div class="grid grid-cols-4 gap-4 mb-6">
    @php
        $pctTotal = $totalAnggaran > 0 ? round(($totalTerpakai / $totalAnggaran) * 100) : 0;
    @endphp
    <div class="bg-white border border-gray-200 rounded-xl p-4">
        <p class="text-xs text-gray-500 mb-1">Total anggaran aktif</p>
        <p class="text-xl font-semibold">Rp {{ number_format($totalAnggaran/1000000, 1) }}jt</p>
        <p class="text-xs text-gray-400 mt-1">{{ $projects->count() }} project berjalan</p>
    </div>
    <div class="bg-white border border-gray-200 rounded-xl p-4">
        <p class="text-xs text-gray-500 mb-1">Total terpakai</p>
        <p class="text-xl font-semibold">Rp {{ number_format($totalTerpakai/1000000, 1) }}jt</p>
        <p class="text-xs text-amber-700 mt-1">{{ $pctTotal }}% dari total</p>
    </div>
    <div class="bg-white border border-gray-200 rounded-xl p-4">
        <p class="text-xs text-gray-500 mb-1">Sisa anggaran</p>
        <p class="text-xl font-semibold">Rp {{ number_format($sisaAnggaran/1000000, 1) }}jt</p>
        <p class="text-xs text-green-700 mt-1">Tersedia</p>
    </div>
    <div class="bg-white border border-gray-200 rounded-xl p-4">
        <p class="text-xs text-gray-500 mb-1">Over budget</p>
        <p class="text-xl font-semibold {{ $overBudget > 0 ? 'text-red-700' : 'text-gray-800' }}">{{ $overBudget }}</p>
        <p class="text-xs {{ $overBudget > 0 ? 'text-red-500' : 'text-gray-400' }} mt-1">
            {{ $overBudget > 0 ? 'Perlu perhatian' : 'Semua aman' }}
        </p>
    </div>
</div>

<div class="grid grid-cols-2 gap-4 mb-4">
    {{-- Budget vs Realisasi --}}
    <div class="bg-white border border-gray-200 rounded-xl p-5">
        <h3 class="text-sm font-medium mb-4">Budget vs realisasi per project</h3>
        @foreach($projects as $project)
            @php
                $pct   = min($project->porsentase_pemakainan, 110);
                $color = match($project->budget_status) {
                    'over'    => 'bg-red-400',
                    'warning' => 'bg-amber-400',
                    default   => 'bg-green-500',
                };
                $textColor = match($project->budget_status) {
                    'over'    => 'text-red-700',
                    'warning' => 'text-amber-700',
                    default   => 'text-green-700',
                };
                $badge = match($project->budget_status) {
                    'over'    => ['Over budget', 'bg-red-100 text-red-700'],
                    'warning' => ['Perhatikan', 'bg-amber-100 text-amber-700'],
                    default   => ['Aman', 'bg-green-100 text-green-700'],
                };
            @endphp
            <div class="mb-4 last:mb-0">
                <div class="flex justify-between items-center mb-1">
                    <span class="text-sm font-medium text-gray-800">{{ $project->nama_project }}</span>
                    <span class="text-xs text-gray-500">
                        Rp {{ number_format($project->total_terpakai/1000000,0) }}jt /
                        Rp {{ number_format($project->budget_total/1000000,0) }}jt
                    </span>
                </div>
                <div class="h-1.5 bg-gray-100 rounded-full overflow-hidden">
                    <div class="h-full rounded-full {{ $color }}" style="width: {{ $pct }}%"></div>
                </div>
                <div class="flex justify-between items-center mt-1">
                    <span class="text-xs {{ $textColor }}">{{ $project->porsentase_pemakainan }}% terpakai</span>
                    <span class="text-xs px-2 py-0.5 rounded-full {{ $badge[1] }}">{{ $badge[0] }}</span>
                </div>
            </div>
        @endforeach
    </div>

    {{-- Distribusi Kategori --}}
    <div class="bg-white border border-gray-200 rounded-xl p-5">
        <h3 class="text-sm font-medium mb-4">Distribusi pengeluaran per kategori</h3>
        @php
            $colors = ['sewa_lokasi'=>'bg-amber-400','honorarium'=>'bg-blue-400','peralatan'=>'bg-teal-400','katering'=>'bg-purple-400','transportasi'=>'bg-orange-400','lain_lain'=>'bg-gray-400'];
            $totalKategori = $distribusiKategori->sum();
        @endphp
        <div class="space-y-3">
            @foreach(\App\Models\Expense::$kategoriLabel as $key => $label)
                @if(isset($distribusiKategori[$key]))
                    @php $pctK = $totalKategori > 0 ? round($distribusiKategori[$key]/$totalKategori*100) : 0; @endphp
                    <div>
                        <div class="flex justify-between text-xs mb-1">
                            <span class="text-gray-600">{{ $label }}</span>
                            <span class="font-medium text-gray-800">Rp {{ number_format($distribusiKategori[$key]/1000000,1) }}jt</span>
                        </div>
                        <div class="h-1.5 bg-gray-100 rounded-full overflow-hidden">
                            <div class="h-full rounded-full {{ $colors[$key] ?? 'bg-gray-300' }}" style="width: {{ $pctK }}%"></div>
                        </div>
                    </div>
                @endif
            @endforeach
            @if($totalKategori == 0)
                <p class="text-xs text-gray-400 text-center py-4">Belum ada pengeluaran approved</p>
            @endif
        </div>
    </div>
</div>

<div class="grid grid-cols-2 gap-4 mb-4">
    {{-- Notifikasi --}}
    <div class="bg-white border border-gray-200 rounded-xl p-5">
        <h3 class="text-sm font-medium mb-4">Notifikasi & peringatan anggaran</h3>
        @forelse($notifikasi as $notif)
            @php
                $dotColor = match($notif->tipe) {
                    'over_budget'       => 'bg-red-400',
                    'warning_80'        => 'bg-amber-400',
                    'invoice_jatuh_tempo' => 'bg-purple-400',
                    default             => 'bg-gray-400',
                };
            @endphp
            <div class="flex gap-3 mb-3 last:mb-0 bg-gray-50 rounded-lg p-3">
                <div class="w-2 h-2 rounded-full mt-1.5 shrink-0 {{ $dotColor }}"></div>
                <div>
                    <p class="text-xs text-gray-800 leading-relaxed">{{ $notif->pesan }}</p>
                    <p class="text-xs text-gray-400 mt-1">{{ $notif->created_at->diffForHumans() }}</p>
                </div>
            </div>
        @empty
            <p class="text-xs text-gray-400 text-center py-4">Tidak ada peringatan aktif</p>
        @endforelse
    </div>

    {{-- Antrian Approval --}}
    <div class="bg-white border border-gray-200 rounded-xl p-5">
        <div class="flex justify-between items-center mb-4">
            <h3 class="text-sm font-medium">Antrian approval pengeluaran</h3>
            @php $pendingCount = \App\Models\Expense::where('status','pending')->count() @endphp
            @if($pendingCount)
                <span class="text-xs bg-purple-100 text-purple-800 px-2 py-0.5 rounded-full">{{ $pendingCount }} menunggu</span>
            @endif
        </div>
        @php $pendingItems = \App\Models\Expense::with(['project','submittedBy'])->where('status','pending')->latest()->take(3)->get() @endphp
        @forelse($pendingItems as $item)
            <div class="flex items-center gap-3 py-2.5 border-b border-gray-100 last:border-0">
                <div class="flex-1 min-w-0">
                    <p class="text-xs font-medium text-gray-800 truncate">{{ $item->nama_pengeluaran }} — {{ $item->project->nama_project }}</p>
                    <p class="text-xs text-gray-400">Rp {{ number_format($item->jumlah,0,',','.') }} · {{ $item->submittedBy->name }}</p>
                </div>
                <div class="flex gap-2 shrink-0">
                    <form method="POST" action="{{ route('budget.expenses.approve', $item) }}" class="inline">
                        @csrf @method('PATCH')
                        <button class="text-xs border border-green-300 text-green-700 rounded px-2 py-1 hover:bg-green-50">Setujui</button>
                    </form>
                    <form method="POST" action="{{ route('budget.expenses.reject', $item) }}" class="inline">
                        @csrf @method('PATCH')
                        <button class="text-xs border border-red-300 text-red-700 rounded px-2 py-1 hover:bg-red-50">Tolak</button>
                    </form>
                </div>
            </div>

    {{-- Hire Major Crew --}}
    <div class="bg-white border border-gray-200 rounded-xl p-5">
        <h3 class="text-sm font-medium text-gray-800 mb-4">Hire Major Crew</h3>
        <form method="POST" action="{{ route('budget.projects.hire-crew', $projects->first()->id ?? 1) }}" class="space-y-3">
            @csrf
            <div>
                <label class="text-xs text-gray-500 block mb-1">Nama Crew *</label>
                <input type="text" name="nama" required placeholder="Contoh: Imam Sutradara"
                    class="w-full text-xs border border-gray-200 rounded-lg px-3 py-2">
            </div>
            <div>
                <label class="text-xs text-gray-500 block mb-1">Role *</label>
                <select name="role" required class="w-full text-xs border border-gray-200 rounded-lg px-3 py-2 bg-white">
                    <option value="">Pilih role...</option>
                    <option value="Sutradara">Sutradara</option>
                    <option value="DoP">DoP (Director of Photography)</option>
                    <option value="Art Director">Art Director</option>
                </select>
            </div>
            <div>
                <label class="text-xs text-gray-500 block mb-1">Gaji per Hari (Rp) *</label>
                <input type="number" name="gaji_per_hari" required min="1" placeholder="1000000"
                    class="w-full text-xs border border-gray-200 rounded-lg px-3 py-2">
            </div>
            <div>
                <label class="text-xs text-gray-500 block mb-1">Total Hari *</label>
                <input type="number" name="total_hari" required min="1" value="1"
                    class="w-full text-xs border border-gray-200 rounded-lg px-3 py-2">
            </div>
            <button type="submit" class="w-full text-xs bg-amber-600 text-white rounded-lg px-4 py-2.5 hover:bg-amber-700 font-medium">
                Hire Crew
            </button>
        </form>
    </div>
        @empty
            <p class="text-xs text-gray-400 text-center py-4">Tidak ada yang menunggu approval</p>
        @endforelse
        @if($pendingCount > 3)
            <a href="{{ route('budget.approvals') }}" class="block text-xs text-center text-amber-700 mt-3 hover:underline">
                Lihat {{ $pendingCount - 3 }} lainnya →
            </a>
        @endif
    </div>
</div>

{{-- Invoice --}}
<div class="bg-white border border-gray-200 rounded-xl p-5">
    <div class="flex justify-between items-center mb-4">
        <h3 class="text-sm font-medium">Invoice vendor — belum dibayar</h3>
        <a href="{{ route('budget.invoices') }}" class="text-xs text-amber-700 hover:underline">Lihat semua →</a>
    </div>
    @forelse($invoices as $inv)
        <div class="flex items-center gap-3 py-2.5 border-b border-gray-100 last:border-0">
            <div class="w-8 h-8 rounded bg-amber-50 text-amber-800 text-xs font-medium flex items-center justify-center shrink-0">INV</div>
            <div class="flex-1 min-w-0">
                <p class="text-xs font-medium text-gray-800">{{ $inv->nomor_invoice }} — {{ $inv->nama_vendor }}</p>
                <p class="text-xs text-gray-400">{{ $inv->project->nama_project }} · Jatuh tempo: {{ $inv->jatuh_tempo->isoFormat('D MMM YYYY') }}</p>
            </div>
            <div class="text-right shrink-0">
                <p class="text-xs font-medium text-gray-800">Rp {{ number_format($inv->jumlah/1000000,1) }}jt</p>
                @if($inv->is_near_due)
                    <span class="text-xs text-amber-700">Segera jatuh tempo</span>
                @elseif($inv->is_overdue)
                    <span class="text-xs text-red-700">Terlambat</span>
                @endif
            </div>
        </div>
    @empty
        <p class="text-xs text-gray-400 text-center py-4">Semua invoice sudah lunas</p>
    @endforelse
</div>
@endsection
