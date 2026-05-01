<?php

namespace App\Http\Controllers;

use App\Models\Crew;
use App\Models\Project;
use App\Models\User;
use App\Traits\AccessControlTrait;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;

class CrewController extends Controller
{
    use AccessControlTrait;

    /**
     * Hire Major Crew (dari Produser atau Admin)
     * ADMIN   → Bisa hire crew untuk semua project
     * PRODUSER → Bisa hire crew hanya untuk project mereka
     * CREW    → TIDAK bisa hire
     */
    public function hireForProject(Request $request, $projectId)
    {
        $user = Auth::user();

        // Validasi: Hanya ADMIN & PRODUSER yang bisa hire
        if (!$user->isAdmin() && !$user->isProduser()) {
            abort(403, 'Anda tidak memiliki akses untuk hire crew.');
        }

        $project = Project::findOrFail($projectId);

        // Validasi: PRODUSER hanya bisa hire untuk project mereka
        if ($user->isProduser() && $project->pic_id !== $user->id) {
            abort(403, 'Anda hanya bisa hire crew untuk project yang Anda kelola.');
        }

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

            // Tentukan role user berdasarkan role crew
            $userRole = ($validated['role'] === 'Produser') ? 'produser' : 'crew';

            // Buat user baru untuk crew dengan role yang tepat
            $user_baru = User::create([
                'name' => $validated['nama'],
                'email' => $email,
                'password' => Hash::make('password'),
                'role' => $userRole,
            ]);

            // Buat crew dengan relasi ke user
            $crew = Crew::create([
                'nama' => $validated['nama'],
                'role' => $validated['role'],
                'email' => $email,
                'user_id' => $user_baru->id,
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
