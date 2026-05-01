@extends('layouts.app')
@section('title', 'Laporan Anggaran')

@section('content')
<div class="mb-6">
    <h2 class="text-lg font-semibold text-gray-800">Laporan Anggaran</h2>
    <p class="text-sm text-gray-500">Ringkasan keuangan per project</p>
</div>

{{-- Pilih Project --}}
<form method="GET" class="mb-6 flex gap-3 items-center">
    <select name="project_id" class="text-xs border border-gray-200 rounded-lg px-3 py-2 bg-white text-gray-700" onchange="this.form.submit()">
        <option value="">Pilih project untuk detail laporan...</option>
        @foreach($projects as $proj)
            <option value="{{ $proj->id }}" {{ request('project_id') == $proj->id ? 'selected' : '' }}>
                {{ $proj->nama_project }}
            </option>
        @endforeach
    </select>
</form>

{{-- Ringkasan semua project --}}
<div class="bg-white border border-gray-200 rounded-xl overflow-hidden mb-4">
    <div class="p-4 border-b border-gray-100">
        <h3 class="text-sm font-medium text-gray-800">Ringkasan semua project</h3>
    </div>
    <table class="w-full text-xs">
        <thead class="bg-gray-50">
            <tr>
                <th class="text-left text-gray-500 font-medium p-3">Project</th>
                <th class="text-right text-gray-500 font-medium p-3">Budget</th>
                <th class="text-right text-gray-500 font-medium p-3">Terpakai</th>
                <th class="text-right text-gray-500 font-medium p-3">Sisa</th>
                <th class="text-center text-gray-500 font-medium p-3">% Pakai</th>
                <th class="text-center text-gray-500 font-medium p-3">Status</th>
            </tr>
        </thead>
        <tbody>
            @foreach($projects as $proj)
                @php
                    $badge = match($proj->budget_status) {
                        'over'    => ['Over budget', 'bg-red-100 text-red-700'],
                        'warning' => ['Perhatikan',  'bg-amber-100 text-amber-700'],
                        default   => ['Aman',         'bg-green-100 text-green-700'],
                    };
                @endphp
                <tr class="border-t border-gray-100 hover:bg-gray-50">
                    <td class="p-3 font-medium text-gray-800">{{ $proj->nama_project }}</td>
                    <td class="p-3 text-right text-gray-700">Rp {{ number_format($proj->budget_total, 0, ',', '.') }}</td>
                    <td class="p-3 text-right text-gray-700">Rp {{ number_format($proj->total_terpakai, 0, ',', '.') }}</td>
                    <td class="p-3 text-right {{ $proj->sisa_anggaran < 0 ? 'text-red-700 font-medium' : 'text-gray-700' }}">
                        Rp {{ number_format($proj->sisa_anggaran, 0, ',', '.') }}
                    </td>
                    <td class="p-3 text-center text-gray-700">{{ $proj->porsentase_pemakainan }}%</td>
                    <td class="p-3 text-center">
                        <span class="px-2 py-0.5 rounded-full {{ $badge[1] }}">{{ $badge[0] }}</span>
                    </td>
                </tr>
            @endforeach
        </tbody>
    </table>
</div>

{{-- Detail Project Terpilih --}}
@if($selectedProject)
    <div class="bg-white border border-gray-200 rounded-xl p-5 mb-4">
        <h3 class="text-sm font-medium text-gray-800 mb-1">Detail: {{ $selectedProject->nama_project }}</h3>
        <p class="text-xs text-gray-400 mb-4">Klien: {{ $selectedProject->client->nama_klien }}</p>

        <div class="grid grid-cols-3 gap-4 mb-6">
            <div class="bg-gray-50 rounded-lg p-3">
                <p class="text-xs text-gray-400">Budget total</p>
                <p class="text-base font-semibold text-gray-800 mt-0.5">Rp {{ number_format($selectedProject->budget_total, 0, ',', '.') }}</p>
            </div>
            <div class="bg-gray-50 rounded-lg p-3">
                <p class="text-xs text-gray-400">Total terpakai (approved)</p>
                <p class="text-base font-semibold text-gray-800 mt-0.5">Rp {{ number_format($selectedProject->total_terpakai, 0, ',', '.') }}</p>
            </div>
            <div class="bg-gray-50 rounded-lg p-3">
                <p class="text-xs text-gray-400">Sisa anggaran</p>
                <p class="text-base font-semibold {{ $selectedProject->sisa_anggaran < 0 ? 'text-red-700' : 'text-gray-800' }} mt-0.5">
                    Rp {{ number_format($selectedProject->sisa_anggaran, 0, ',', '.') }}
                </p>
            </div>
        </div>

        <h4 class="text-xs font-medium text-gray-600 mb-3">Pengeluaran per kategori</h4>
        @php $totalKat = array_sum($expensesByKategori); @endphp
        <div class="space-y-2">
            @foreach(\App\Models\Expense::$kategoriLabel as $key => $label)
                @if(isset($expensesByKategori[$key]))
                    @php $pct = $totalKat > 0 ? round($expensesByKategori[$key]/$totalKat*100) : 0; @endphp
                    <div class="flex items-center gap-3">
                        <span class="text-xs text-gray-600 w-40">{{ $label }}</span>
                        <div class="flex-1 h-1.5 bg-gray-100 rounded-full overflow-hidden">
                            <div class="h-full bg-amber-400 rounded-full" style="width: {{ $pct }}%"></div>
                        </div>
                        <span class="text-xs text-gray-700 w-28 text-right">Rp {{ number_format($expensesByKategori[$key], 0, ',', '.') }}</span>
                        <span class="text-xs text-gray-400 w-8">{{ $pct }}%</span>
                    </div>
                @endif
            @endforeach
        </div>
    </div>

    {{-- Tabel semua pengeluaran project ini --}}
    <div class="bg-white border border-gray-200 rounded-xl p-5">
        <h3 class="text-sm font-medium text-gray-800 mb-4">Semua pengeluaran — {{ $selectedProject->nama_project }}</h3>
        <table class="w-full text-xs">
            <thead>
                <tr class="border-b border-gray-100">
                    <th class="text-left text-gray-500 font-medium pb-2 pr-3">Nama</th>
                    <th class="text-left text-gray-500 font-medium pb-2 pr-3">Kategori</th>
                    <th class="text-left text-gray-500 font-medium pb-2 pr-3">Tanggal</th>
                    <th class="text-right text-gray-500 font-medium pb-2 pr-3">Jumlah</th>
                    <th class="text-center text-gray-500 font-medium pb-2">Status</th>
                </tr>
            </thead>
            <tbody>
                @forelse($selectedProject->expenses as $exp)
                    @php
                        $bc = match($exp->status) {
                            'approved' => 'bg-green-100 text-green-700',
                            'rejected' => 'bg-red-100 text-red-700',
                            default    => 'bg-purple-100 text-purple-700',
                        };
                    @endphp
                    <tr class="border-b border-gray-50">
                        <td class="py-2 pr-3 text-gray-800">{{ $exp->nama_pengeluaran }}</td>
                        <td class="py-2 pr-3 text-gray-600">{{ $exp->kategori_label }}</td>
                        <td class="py-2 pr-3 text-gray-500">{{ $exp->tanggal_pengeluaran->isoFormat('D MMM YYYY') }}</td>
                        <td class="py-2 pr-3 text-right font-medium text-gray-800">Rp {{ number_format($exp->jumlah, 0, ',', '.') }}</td>
                        <td class="py-2 text-center"><span class="px-2 py-0.5 rounded-full {{ $bc }}">{{ ucfirst($exp->status) }}</span></td>
                    </tr>
                @empty
                    <tr><td colspan="5" class="py-4 text-center text-gray-400">Belum ada pengeluaran</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>
@endif
@endsection
