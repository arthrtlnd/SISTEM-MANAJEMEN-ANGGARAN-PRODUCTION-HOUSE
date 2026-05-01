@extends('layouts.app')
@section('title', 'Invoice Vendor')

@section('content')
<div class="mb-6 flex justify-between items-center">
    <div>
        <h2 class="text-lg font-semibold text-gray-800">Invoice Vendor</h2>
        <p class="text-sm text-gray-500">Kelola tagihan dari vendor eksternal</p>
    </div>
</div>

<div class="grid grid-cols-3 gap-4">
    {{-- Tabel Invoice --}}
    <div class="col-span-2 bg-white border border-gray-200 rounded-xl overflow-hidden">
        <table class="w-full text-xs">
            <thead class="bg-gray-50">
                <tr>
                    <th class="text-left text-gray-500 font-medium p-4">No. Invoice</th>
                    <th class="text-left text-gray-500 font-medium p-4">Vendor & Project</th>
                    <th class="text-left text-gray-500 font-medium p-4">Jatuh Tempo</th>
                    <th class="text-right text-gray-500 font-medium p-4">Jumlah</th>
                    <th class="text-center text-gray-500 font-medium p-4">Status & Aksi</th>
                </tr>
            </thead>
            <tbody>
                @forelse($invoices as $inv)
                    @php
                        $isOverdue  = $inv->is_overdue;
                        $isNearDue  = $inv->is_near_due;
                    @endphp
                    <tr class="border-t border-gray-100 hover:bg-gray-50 {{ $isOverdue ? 'bg-red-50' : '' }}">
                        <td class="p-4">
                            <p class="font-medium text-gray-800">{{ $inv->nomor_invoice }}</p>
                        </td>
                        <td class="p-4">
                            <p class="font-medium text-gray-800">{{ $inv->nama_vendor }}</p>
                            <p class="text-gray-400">{{ $inv->project->nama_project }}</p>
                        </td>
                        <td class="p-4">
                            <p class="{{ $isOverdue ? 'text-red-700 font-medium' : ($isNearDue ? 'text-amber-700' : 'text-gray-600') }}">
                                {{ $inv->jatuh_tempo->isoFormat('D MMM YYYY') }}
                            </p>
                            @if($isOverdue)
                                <p class="text-red-500 text-xs">Terlambat</p>
                            @elseif($isNearDue)
                                <p class="text-amber-600 text-xs">Segera jatuh tempo</p>
                            @endif
                        </td>
                        <td class="p-4 text-right font-medium text-gray-800">
                            Rp {{ number_format($inv->jumlah, 0, ',', '.') }}
                        </td>
                        <td class="p-4 text-center">
                            @if($inv->status === 'belum_bayar')
                                <div class="flex flex-col items-center gap-1">
                                    <span class="text-xs px-2 py-0.5 rounded-full bg-amber-100 text-amber-700">Belum bayar</span>
                                    <form method="POST" action="{{ route('budget.invoices.bayar', $inv) }}" class="inline">
                                        @csrf @method('PATCH')
                                        <button class="text-xs text-green-700 hover:underline mt-0.5">Tandai lunas</button>
                                    </form>
                                </div>
                            @else
                                <div>
                                    <span class="text-xs px-2 py-0.5 rounded-full bg-green-100 text-green-700">Lunas</span>
                                    <p class="text-xs text-gray-400 mt-0.5">{{ $inv->tanggal_bayar?->isoFormat('D MMM') }}</p>
                                </div>
                            @endif
                        </td>
                    </tr>
                @empty
                    <tr><td colspan="5" class="py-8 text-center text-gray-400">Belum ada invoice</td></tr>
                @endforelse
            </tbody>
        </table>
        <div class="p-4 border-t border-gray-100">{{ $invoices->links() }}</div>
    </div>

    {{-- Form Tambah Invoice --}}
    <div class="bg-white border border-gray-200 rounded-xl p-5 h-fit">
        <h3 class="text-sm font-medium text-gray-800 mb-4">Tambah invoice baru</h3>
        <form method="POST" action="{{ route('budget.invoices.store') }}" enctype="multipart/form-data">
            @csrf
            <div class="mb-3">
                <label class="text-xs text-gray-500 block mb-1">Project *</label>
                <select name="project_id" required class="w-full text-xs border border-gray-200 rounded-lg px-3 py-2 bg-white">
                    <option value="">Pilih project...</option>
                    @foreach($projects as $proj)
                        <option value="{{ $proj->id }}">{{ $proj->nama_project }}</option>
                    @endforeach
                </select>
            </div>
            <div class="mb-3">
                <label class="text-xs text-gray-500 block mb-1">Nama vendor *</label>
                <input type="text" name="nama_vendor" required placeholder="Contoh: Studio A"
                    class="w-full text-xs border border-gray-200 rounded-lg px-3 py-2">
            </div>
            <div class="mb-3">
                <label class="text-xs text-gray-500 block mb-1">Jumlah (Rp) *</label>
                <input type="number" name="jumlah" required min="1" placeholder="0"
                    class="w-full text-xs border border-gray-200 rounded-lg px-3 py-2">
            </div>
            <div class="mb-3">
                <label class="text-xs text-gray-500 block mb-1">Tanggal invoice *</label>
                <input type="date" name="tanggal_invoice" required value="{{ now()->toDateString() }}"
                    class="w-full text-xs border border-gray-200 rounded-lg px-3 py-2">
            </div>
            <div class="mb-3">
                <label class="text-xs text-gray-500 block mb-1">Jatuh tempo *</label>
                <input type="date" name="jatuh_tempo" required
                    class="w-full text-xs border border-gray-200 rounded-lg px-3 py-2">
            </div>
            <div class="mb-4">
                <label class="text-xs text-gray-500 block mb-1">File invoice (PDF/Foto)</label>
                <input type="file" name="file_invoice" accept=".pdf,.jpg,.jpeg,.png"
                    class="w-full text-xs text-gray-600">
            </div>
            <button type="submit"
                class="w-full text-xs bg-amber-600 text-white rounded-lg px-4 py-2.5 hover:bg-amber-700 font-medium">
                Simpan invoice
            </button>
        </form>
    </div>
</div>
@endsection
