<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Models\BudgetNotification;
use App\Models\Expense;
use App\Models\Invoice;
use App\Models\Project;
use App\Traits\AccessControlTrait;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;

class BudgetController extends Controller
{
    use AccessControlTrait;

    // ─────────────────────────────────────────
    // DASHBOARD
    // ─────────────────────────────────────────

    public function index(): View
    {
        $user = Auth::user();
        $projects = $this->getProjectsForUser();

        $totalAnggaran = $projects->sum('budget_total');
        $totalTerpakai = $projects->sum('total_terpakai');
        $sisaAnggaran  = $totalAnggaran - $totalTerpakai;
        $overBudget    = $projects->filter(fn($p) => $p->budget_status === 'over')->count();

        // Filter distribusi kategori berdasarkan projects yang bisa diakses
        $projectIds = $projects->pluck('id');
        $distribusiKategori = Expense::whereIn('project_id', $projectIds)
            ->where('status', 'approved')
            ->selectRaw('kategori, SUM(jumlah) as total')
            ->groupBy('kategori')
            ->pluck('total', 'kategori');

        // Filter pengeluaran terbaru
        $pengeluaranTerbaru = Expense::whereIn('project_id', $projectIds)
            ->with(['project', 'submittedBy'])
            ->latest()->take(10)->get();

        $notifikasi = BudgetNotification::whereIn('project_id', $projectIds)
            ->where('is_read', false)->latest()->take(5)->get();

        $invoices = Invoice::whereIn('project_id', $projectIds)
            ->where('status', 'belum_bayar')
            ->orderBy('jatuh_tempo')->take(5)->get();

        return view('budget.index', compact(
            'projects','totalAnggaran','totalTerpakai',
            'sisaAnggaran','overBudget','distribusiKategori',
            'pengeluaranTerbaru','notifikasi','invoices'
        ));
    }

    // ─────────────────────────────────────────
    // PENGELUARAN
    // ─────────────────────────────────────────

    public function expenses(Request $request): View
    {
        $user = Auth::user();
        $query = Expense::with(['project', 'submittedBy', 'approvedBy'])->latest();

        // Filter berdasarkan role
        if ($user->isProduser()) {
            // Produser hanya lihat expense dari project mereka
            $query->whereHas('project', fn($q) => $q->where('pic_id', $user->id));
        } else if ($user->isCrew()) {
            // Crew hanya lihat expense yang mereka submit
            $query->where('submitted_by', $user->id);
        }
        // Admin lihat semua (no filter)

        // Additional filters
        if ($request->filled('project_id')) $query->where('project_id', $request->project_id);
        if ($request->filled('kategori'))   $query->where('kategori', $request->kategori);
        if ($request->filled('status'))     $query->where('status', $request->status);

        $expenses = $query->paginate(15);
        $projects = $this->getProjectsForUser();

        return view('budget.expenses', compact('expenses', 'projects'));
    }

    public function storeExpense(Request $request): RedirectResponse
    {
        $user = Auth::user();

        $validated = $request->validate([
            'project_id'          => 'required|exists:projects,id',
            'nama_pengeluaran'    => 'required|string|max:255',
            'kategori'            => 'required|in:sewa_lokasi,honorarium,peralatan,katering,transportasi,lain_lain',
            'jumlah'              => 'required|numeric|min:1',
            'tanggal_pengeluaran' => 'required|date',
            'keterangan'          => 'nullable|string',
            'bukti_file'          => 'nullable|file|mimes:pdf,jpg,jpeg,png|max:5120',
        ]);

        // Validasi: Crew tidak bisa input pengeluaran >= Rp 1jt
        if ($user->isCrew() && $validated['jumlah'] >= 1000000) {
            return back()->withErrors(['jumlah' => 'Pengeluaran Anda melebihi batas maksimal Rp 1.000.000. Hubungi produser untuk persetujuan.']);
        }

        // Validasi: Produser hanya bisa input untuk project mereka
        if ($user->isProduser()) {
            $project = Project::findOrFail($validated['project_id']);
            if ($project->pic_id !== $user->id) {
                abort(403, 'Anda hanya bisa input pengeluaran untuk project yang Anda kelola.');
            }
        }

        $validated['submitted_by'] = Auth::id();
        $validated['status']       = 'pending';

        if ($request->hasFile('bukti_file')) {
            $validated['bukti_file'] = $request->file('bukti_file')
                ->store('bukti_pengeluaran', 'public');
        }

        Expense::create($validated);
        $this->checkBudgetAlert($validated['project_id']);

        return redirect()->route('budget.expenses')
            ->with('success', 'Pengeluaran berhasil diajukan, menunggu approval.');
    }

    public function approveExpense(Expense $expense): RedirectResponse
    {
        $user = Auth::user();

        // Validasi: Hanya yang berhak yang bisa approve
        if (!$this->canApproveExpense($expense)) {
            abort(403, 'Anda tidak memiliki akses untuk approve pengeluaran ini.');
        }

        $expense->update([
            'status'      => 'approved',
            'approved_by' => Auth::id(),
            'approved_at' => now(),
        ]);
        $this->checkBudgetAlert($expense->project_id);

        return back()->with('success', 'Pengeluaran disetujui.');
    }

    public function rejectExpense(Expense $expense): RedirectResponse
    {
        $user = Auth::user();

        // Validasi: Hanya yang berhak yang bisa reject
        if (!$this->canApproveExpense($expense)) {
            abort(403, 'Anda tidak memiliki akses untuk reject pengeluaran ini.');
        }

        $expense->update([
            'status'      => 'rejected',
            'approved_by' => Auth::id(),
            'approved_at' => now(),
        ]);

        return back()->with('success', 'Pengeluaran ditolak.');
    }

    // ─────────────────────────────────────────
    // APPROVAL QUEUE
    // ─────────────────────────────────────────

    public function approvals(): View
    {
        $user = Auth::user();
        $query = Expense::with(['project', 'submittedBy'])->where('status', 'pending');

        // Filter berdasarkan role
        if ($user->isProduser()) {
            // Produser lihat pengeluaran dari project mereka
            $query->whereHas('project', fn($q) => $q->where('pic_id', $user->id));
        } else if ($user->isAdmin()) {
            // Admin lihat semua pending dengan jumlah >= Rp 1jt
            // (karena produser yang handle < Rp 1jt)
            $query->where('jumlah', '>=', 1000000);
        } else {
            // Crew tidak bisa lihat
            abort(403, 'Anda tidak memiliki akses ke halaman approval.');
        }

        $pending = $query->latest()->paginate(15);

        return view('budget.approvals', compact('pending'));
    }

    // ─────────────────────────────────────────
    // INVOICE
    // ─────────────────────────────────────────

    public function invoices(): View
    {
        $user = Auth::user();
        $query = Invoice::with('project')->latest();

        // Filter berdasarkan role
        if ($user->isProduser()) {
            // Produser hanya lihat invoice dari project mereka
            $query->whereHas('project', fn($q) => $q->where('pic_id', $user->id));
        } else if ($user->isCrew()) {
            // Crew tidak bisa lihat invoice
            abort(403, 'Anda tidak memiliki akses ke halaman invoice.');
        }
        // Admin lihat semua (no filter)

        $invoices = $query->paginate(15);
        $projects = $this->getProjectsForUser();

        return view('budget.invoices', compact('invoices', 'projects'));
    }

    public function storeInvoice(Request $request): RedirectResponse
    {
        $user = Auth::user();

        if ($user->isCrew()) {
            abort(403, 'Anda tidak memiliki akses untuk membuat invoice.');
        }

        $validated = $request->validate([
            'project_id'      => 'required|exists:projects,id',
            'nama_vendor'     => 'required|string|max:255',
            'jumlah'          => 'required|numeric|min:1',
            'tanggal_invoice' => 'required|date',
            'jatuh_tempo'     => 'required|date|after_or_equal:tanggal_invoice',
            'keterangan'      => 'nullable|string',
            'file_invoice'    => 'nullable|file|mimes:pdf,jpg,jpeg,png|max:5120',
        ]);

        // Validasi: Produser hanya bisa buat invoice untuk project mereka
        if ($user->isProduser()) {
            $project = Project::findOrFail($validated['project_id']);
            if ($project->pic_id !== $user->id) {
                abort(403, 'Anda hanya bisa buat invoice untuk project yang Anda kelola.');
            }
        }

        $lastId = Invoice::max('id') ?? 0;
        $validated['nomor_invoice'] = 'INV-' . date('Y') . '-' . str_pad($lastId + 1, 3, '0', STR_PAD_LEFT);
        $validated['status'] = 'belum_bayar';

        if ($request->hasFile('file_invoice')) {
            $validated['file_invoice'] = $request->file('file_invoice')->store('invoices', 'public');
        }

        Invoice::create($validated);
        return redirect()->route('budget.invoices')->with('success', 'Invoice berhasil ditambahkan.');
    }

    public function bayarInvoice(Invoice $invoice): RedirectResponse
    {
        $user = Auth::user();

        if ($user->isCrew()) {
            abort(403, 'Anda tidak memiliki akses untuk bayar invoice.');
        }

        // Produser hanya bisa bayar invoice dari project mereka
        if ($user->isProduser() && $invoice->project->pic_id !== $user->id) {
            abort(403, 'Anda hanya bisa bayar invoice dari project yang Anda kelola.');
        }

        $invoice->update(['status' => 'lunas', 'tanggal_bayar' => now()->toDateString()]);
        return back()->with('success', 'Invoice ditandai lunas.');
    }

    // ─────────────────────────────────────────
    // LAPORAN
    // ─────────────────────────────────────────

    public function laporan(Request $request): View
    {
        $user = Auth::user();
        $projects = $this->getProjectsForUser();
        $selectedProject = null;
        $expensesByKategori = [];

        if ($request->filled('project_id')) {
            $selectedProject = $projects->find($request->project_id);

            if (!$selectedProject) {
                abort(403, 'Anda tidak memiliki akses ke project ini.');
            }

            $expensesByKategori = $selectedProject->expenses()
                ->where('status', 'approved')
                ->selectRaw('kategori, SUM(jumlah) as total')
                ->groupBy('kategori')
                ->pluck('total', 'kategori')
                ->toArray();
        }

        return view('budget.laporan', compact('projects', 'selectedProject', 'expensesByKategori'));
    }

    // ─────────────────────────────────────────
    // HELPER: CEK ALERT BUDGET
    // ─────────────────────────────────────────

    private function checkBudgetAlert(int $projectId): void
    {
        $project = Project::findOrFail($projectId);
        $pct     = $project->porsentase_pemakainan;

        if ($pct > 100) {
            BudgetNotification::firstOrCreate(
                ['project_id' => $projectId, 'tipe' => 'over_budget'],
                ['pesan' => "{$project->nama_project} melebihi budget (pemakaian {$pct}%). Segera koordinasi dengan klien.", 'is_read' => false]
            );
        } elseif ($pct >= 80) {
            BudgetNotification::firstOrCreate(
                ['project_id' => $projectId, 'tipe' => 'warning_80'],
                ['pesan' => "{$project->nama_project} telah mencapai {$pct}% anggaran. Sisa Rp " . number_format($project->sisa_anggaran, 0, ',', '.'), 'is_read' => false]
            );
        }
    }
}
