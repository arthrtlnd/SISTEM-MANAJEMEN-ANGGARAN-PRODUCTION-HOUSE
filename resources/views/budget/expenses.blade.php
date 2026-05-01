@extends('layouts.app')
@section('title', 'Pengeluaran')

@section('content')
<div class="mb-6 flex justify-between items-center">
    <div>
        <h2 class="text-lg font-semibold text-gray-800">Daftar Pengeluaran</h2>
        <p class="text-sm text-gray-500">Semua transaksi pengeluaran project</p>
    </div>
</div>

<div class="grid grid-cols-3 gap-4">
    {{-- Tabel Pengeluaran --}}
    <div class="col-span-2 bg-white border border-gray-200 rounded-xl overflow-hidden">
        {{-- Filter --}}
        <div class="p-4 border-b border-gray-100">
            <form method="GET" class="flex gap-3">
                <select name="project_id" class="text-xs border border-gray-200 rounded-lg px-3 py-2 bg-white text-gray-600">
                    <option value="">Semua project</option>
                    @foreach($projects as $proj)
                        <option value="{{ $proj->id }}" {{ request('project_id') == $proj->id ? 'selected' : '' }}>
                            {{ $proj->nama_project }}
                        </option>
                    @endforeach
                </select>
                <select name="kategori" class="text-xs border border-gray-200 rounded-lg px-3 py-2 bg-white text-gray-600">
                    <option value="">Semua kategori</option>
                    @foreach(\App\Models\Expense::$kategoriLabel as $val => $label)
                        <option value="{{ $val }}" {{ request('kategori') == $val ? 'selected' : '' }}>{{ $label }}</option>
                    @endforeach
                </select>
                <select name="status" class="text-xs border border-gray-200 rounded-lg px-3 py-2 bg-white text-gray-600">
                    <option value="">Semua status</option>
                    <option value="pending" {{ request('status')=='pending' ? 'selected':'' }}>Pending</option>
                    <option value="approved" {{ request('status')=='approved' ? 'selected':'' }}>Approved</option>
                    <option value="rejected" {{ request('status')=='rejected' ? 'selected':'' }}>Rejected</option>
                </select>
                <button type="submit" class="text-xs bg-amber-600 text-white rounded-lg px-4 py-2 hover:bg-amber-700">Filter</button>
                <a href="{{ route('budget.expenses') }}" class="text-xs border border-gray-200 rounded-lg px-3 py-2 text-gray-500 hover:bg-gray-50">Reset</a>
            </form>
        </div>

        {{-- Tabel --}}
        <div class="overflow-x-auto">
            <table class="w-full text-xs">
                <thead class="bg-gray-50">
                    <tr>
                        <th class="text-left text-gray-500 font-medium p-3">Pengeluaran</th>
                        <th class="text-left text-gray-500 font-medium p-3">Project</th>
                        <th class="text-left text-gray-500 font-medium p-3">Kategori</th>
                        <th class="text-right text-gray-500 font-medium p-3">Jumlah</th>
                        <th class="text-center text-gray-500 font-medium p-3">Status</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($expenses as $exp)
                        @php
                            $badgeClass = match($exp->status) {
                                'approved' => 'bg-green-100 text-green-700',
                                'rejected' => 'bg-red-100 text-red-700',
                                default    => 'bg-purple-100 text-purple-700',
                            };
                            $badgeLabel = match($exp->status) {
                                'approved' => 'Approved',
                                'rejected' => 'Ditolak',
                                default    => 'Pending',
                            };
                        @endphp
                        <tr class="border-t border-gray-100 hover:bg-gray-50">
                            <td class="p-3">
                                <p class="font-medium text-gray-800">{{ $exp->nama_pengeluaran }}</p>
                                <p class="text-gray-400 text-xs mt-0.5">{{ $exp->submittedBy->name }} · {{ $exp->tanggal_pengeluaran->isoFormat('D MMM') }}</p>
                            </td>
                            <td class="p-3 text-gray-600">{{ Str::limit($exp->project->nama_project, 20) }}</td>
                            <td class="p-3 text-gray-600">{{ $exp->kategori_label }}</td>
                            <td class="p-3 text-right font-medium text-gray-800">
                                Rp {{ number_format($exp->jumlah, 0, ',', '.') }}
                            </td>
                            <td class="p-3 text-center">
                                <span class="px-2 py-0.5 rounded-full {{ $badgeClass }}">{{ $badgeLabel }}</span>
                            </td>
                        </tr>
                    @empty
                        <tr><td colspan="5" class="py-8 text-center text-gray-400">Tidak ada data pengeluaran</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        <div class="p-4 border-t border-gray-100">{{ $expenses->withQueryString()->links() }}</div>
    </div>

    {{-- Form Input Pengeluaran --}}
    <div class="bg-white border border-gray-200 rounded-xl p-5 h-fit">
        <h3 class="text-sm font-medium text-gray-800 mb-4">Input pengeluaran baru</h3>
        <form method="POST" action="{{ route('budget.expenses.store') }}" enctype="multipart/form-data">
            @csrf
            <div class="mb-3">
                <label class="text-xs text-gray-500 block mb-1">Project *</label>
                <select name="project_id" required class="w-full text-xs border border-gray-200 rounded-lg px-3 py-2 bg-white">
                    <option value="">Pilih project...</option>
                    @foreach($projects as $proj)
                        <option value="{{ $proj->id }}" {{ old('project_id') == $proj->id ? 'selected' : '' }}>
                            {{ $proj->nama_project }}
                        </option>
                    @endforeach
                </select>
            </div>
            <div class="mb-3">
                <label class="text-xs text-gray-500 block mb-1">Nama pengeluaran *</label>
                <input type="text" name="nama_pengeluaran" value="{{ old('nama_pengeluaran') }}" required
                    placeholder="Contoh: Sewa Studio A"
                    class="w-full text-xs border border-gray-200 rounded-lg px-3 py-2">
            </div>
            <div class="mb-3">
                <label class="text-xs text-gray-500 block mb-1">Kategori *</label>
                <select name="kategori" required class="w-full text-xs border border-gray-200 rounded-lg px-3 py-2 bg-white">
                    <option value="">Pilih kategori...</option>
                    @foreach(\App\Models\Expense::$kategoriLabel as $val => $label)
                        <option value="{{ $val }}" {{ old('kategori') == $val ? 'selected' : '' }}>{{ $label }}</option>
                    @endforeach
                </select>
            </div>
            <div class="mb-3">
                <label class="text-xs text-gray-500 block mb-1">Jumlah (Rp) *</label>
                <input type="number" name="jumlah" value="{{ old('jumlah') }}" required min="1"
                    placeholder="0"
                    class="w-full text-xs border border-gray-200 rounded-lg px-3 py-2">
            </div>
            <div class="mb-3">
                <label class="text-xs text-gray-500 block mb-1">Tanggal pengeluaran *</label>
                <input type="date" name="tanggal_pengeluaran" value="{{ old('tanggal_pengeluaran', now()->toDateString()) }}" required
                    class="w-full text-xs border border-gray-200 rounded-lg px-3 py-2">
            </div>
            <div class="mb-3">
                <label class="text-xs text-gray-500 block mb-1">Keterangan</label>
                <textarea name="keterangan" rows="2" placeholder="Detail tambahan..."
                    class="w-full text-xs border border-gray-200 rounded-lg px-3 py-2 resize-none">{{ old('keterangan') }}</textarea>
            </div>
            <div class="mb-4">
                <label class="text-xs text-gray-500 block mb-1">Bukti (PDF/Foto, maks 5MB)</label>
                <input type="file" name="bukti_file" accept=".pdf,.jpg,.jpeg,.png"
                    class="w-full text-xs text-gray-600">
            </div>
            <button type="submit"
                class="w-full text-xs bg-amber-600 text-white rounded-lg px-4 py-2.5 hover:bg-amber-700 font-medium">
                Ajukan untuk approval
            </button>
        </form>
    </div>
</div>
@endsection
