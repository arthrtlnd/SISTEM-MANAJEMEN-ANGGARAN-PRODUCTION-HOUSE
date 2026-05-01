@extends('layouts.app')
@section('title', 'Hire Major Crew')

@section('content')
<div class="mb-6">
    <h2 class="text-lg font-semibold text-gray-800">Hire Major Crew</h2>
    <p class="text-sm text-gray-500">Rekrut crew utama untuk project</p>
</div>

<div class="grid grid-cols-3 gap-4">
    {{-- Daftar Project --}}
    <div class="col-span-1 bg-white border border-gray-200 rounded-xl p-5">
        <h3 class="text-sm font-medium mb-4">Pilih Project</h3>
        <div class="space-y-2">
            @foreach($projects as $proj)
                <div class="p-3 border border-gray-200 rounded-lg hover:bg-gray-50 cursor-pointer"
                     onclick="selectProject({{ $proj->id }})">
                    <p class="text-xs font-medium text-gray-800">{{ $proj->nama_project }}</p>
                    <p class="text-xs text-gray-400">{{ $proj->client->nama_klien }}</p>
                </div>
            @endforeach
        </div>
    </div>

    {{-- Form Hire Crew --}}
    <div class="col-span-2 bg-white border border-gray-200 rounded-xl p-5">
        <h3 class="text-sm font-medium mb-4">Tambah Crew Baru</h3>

        <form id="hireCrewForm" method="POST" class="space-y-4">
            @csrf
            <input type="hidden" id="projectId" name="project_id" value="">

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
                    <option value="Produser">Produser</option>
                </select>
            </div>

            <div class="grid grid-cols-2 gap-3">
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
            </div>

            <button type="submit" class="w-full text-xs bg-amber-600 text-white rounded-lg px-4 py-3 hover:bg-amber-700 font-medium">
                Hire Crew
            </button>
        </form>
    </div>
</div>

<script>
function selectProject(projectId) {
    document.getElementById('projectId').value = projectId;
    document.getElementById('hireCrewForm').action = '/budget/hire-crew/' + projectId;

    // Highlight selected project
    document.querySelectorAll('[onclick*="selectProject"]').forEach(el => {
        el.classList.remove('bg-amber-50', 'border-amber-300');
        el.classList.add('border-gray-200');
    });
    event.currentTarget.classList.add('bg-amber-50', 'border-amber-300');
}
</script>
@endsection
