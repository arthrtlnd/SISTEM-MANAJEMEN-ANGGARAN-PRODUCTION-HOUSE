@extends('layouts.app')
@section('title', 'Approval Pengeluaran')

@section('content')
<div class="mb-6">
    <h2 class="text-lg font-semibold text-gray-800">Approval Pengeluaran</h2>
    <p class="text-sm text-gray-500">Pengeluaran yang menunggu persetujuan</p>
</div>

<div class="bg-white border border-gray-200 rounded-xl overflow-hidden">
    <table class="w-full text-xs">
        <thead class="bg-gray-50">
            <tr>
                <th class="text-left text-gray-500 font-medium p-4">Pengeluaran</th>
                <th class="text-left text-gray-500 font-medium p-4">Project</th>
                <th class="text-left text-gray-500 font-medium p-4">Kategori</th>
                <th class="text-right text-gray-500 font-medium p-4">Jumlah</th>
                <th class="text-left text-gray-500 font-medium p-4">Diajukan oleh</th>
                <th class="text-center text-gray-500 font-medium p-4">Aksi</th>
            </tr>
        </thead>
        <tbody>
            @forelse($pending as $item)
                <tr class="border-t border-gray-100 hover:bg-gray-50">
                    <td class="p-4">
                        <p class="font-medium text-gray-800">{{ $item->nama_pengeluaran }}</p>
                        <p class="text-gray-400 mt-0.5">{{ $item->tanggal_pengeluaran->isoFormat('D MMM YYYY') }}</p>
                        @if($item->keterangan)
                            <p class="text-gray-400 mt-0.5 italic text-xs">{{ Str::limit($item->keterangan, 50) }}</p>
                        @endif
                    </td>
                    <td class="p-4 text-gray-600">{{ $item->project->nama_project }}</td>
                    <td class="p-4 text-gray-600">{{ $item->kategori_label }}</td>
                    <td class="p-4 text-right font-semibold text-gray-800">
                        Rp {{ number_format($item->jumlah, 0, ',', '.') }}
                    </td>
                    <td class="p-4 text-gray-600">{{ $item->submittedBy->name }}</td>
                    <td class="p-4">
                        <div class="flex gap-2 justify-center">
                            <form method="POST" action="{{ route('budget.expenses.approve', $item) }}" class="inline">
                                @csrf @method('PATCH')
                                <button class="text-xs bg-green-50 border border-green-300 text-green-700 rounded-lg px-3 py-1.5 hover:bg-green-100">
                                    Setujui
                                </button>
                            </form>
                            <form method="POST" action="{{ route('budget.expenses.reject', $item) }}" class="inline">
                                @csrf @method('PATCH')
                                <button class="text-xs bg-red-50 border border-red-300 text-red-700 rounded-lg px-3 py-1.5 hover:bg-red-100">
                                    Tolak
                                </button>
                            </form>
                        </div>
                    </td>
                </tr>
            @empty
                <tr>
                    <td colspan="6" class="p-12 text-center text-gray-400">
                        Tidak ada pengeluaran yang menunggu approval
                    </td>
                </tr>
            @endforelse
        </tbody>
    </table>
    <div class="p-4 border-t border-gray-100">{{ $pending->links() }}</div>
</div>
@endsection
