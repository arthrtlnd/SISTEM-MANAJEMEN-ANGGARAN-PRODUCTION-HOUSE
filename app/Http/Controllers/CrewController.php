<?php

namespace App\Http\Controllers;

use App\Models\Crew;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;

class CrewController extends Controller
{
    // Hire Major Crew (dari Produser)
    public function hireForProject(Request $request, $projectId)
{
    $validated = $request->validate([
        'nama' => 'required|string|max:255',
        'role' => 'required|in:Sutradara,DoP,Art Director,Produser',
        'gaji_per_hari' => 'required|numeric|min:1',
        'total_hari' => 'required|integer|min:1',
    ]);

    // Cek apakah crew dengan nama yang sama sudah ada
    $existingCrew = Crew::where('nama', $validated['nama'])->first();

    if ($existingCrew) {
        $crew = $existingCrew;
    } else {
        // Buat email yang unique
        $baseEmail = strtolower(str_replace(' ', '', $validated['nama'])) . '@ph.com';
        $email = $baseEmail;
        $counter = 1;

            // Jika email sudah ada, tambah angka di belakang
            while (User::where('email', $email)->exists()) {
                $email = strtolower(str_replace(' ', '', $validated['nama'])) . $counter . '@ph.com';
                $counter++;
            }

    // Buat user baru untuk crew
    $email = strtolower(str_replace(' ', '', $validated['nama'])) . '@ph.com';

    $user = User::create([
        'name' => $validated['nama'],
        'email' => $email,
        'password' => Hash::make('password'),
        'role' => 'crew',
    ]);

    // Buat crew
    $crew = Crew::create([
        'nama' => $validated['nama'],
        'role' => $validated['role'],
        'email' => $email,
        'user_id' => $user->id,
    ]);
}

    // Assign crew ke project + input gaji
    $projectCrew = $crew->projectCrews()->create([
        'project_id' => $projectId,
        'gaji_per_hari' => $validated['gaji_per_hari'],
        'total_hari' => $validated['total_hari'],
        'total_gaji' => $validated['gaji_per_hari'] * $validated['total_hari'],
    ]);

    // Auto create expense entry untuk gaji crew
    \App\Models\Expense::create([
        'project_id' => $projectId,
        'submitted_by' => Auth::id(),
        'approved_by' => Auth::id(),
        'nama_pengeluaran' => "Gaji " . $validated['role'] . " (" . $validated['nama'] . ")",
        'kategori' => 'honorarium',
        'jumlah' => $projectCrew->total_gaji,
        'status' => 'approved',
        'tanggal_pengeluaran' => now()->toDateString(),
    ]);

    return redirect()->back()->with('success', 'Crew ' . $validated['nama'] . ' berhasil di-hire!');
    }
}
