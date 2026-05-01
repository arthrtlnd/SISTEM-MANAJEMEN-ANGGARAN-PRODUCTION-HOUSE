<?php

namespace App\Http\Controllers;

// ← PENTING: ini yang fix error "Controller not found"
use App\Http\Controllers\Controller;
use App\Models\BudgetNotification;
use App\Models\Expense;
use App\Models\Invoice;
use App\Models\Project;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;

class BudgetController extends Controller
{
    // ─────────────────────────────────────────
    // DASHBOARD
    // ─────────────────────────────────────────

    public function index(): View
    {
        $projects = Project::with(['expenses', 'client'])->get();

        $totalAnggaran = $projects->sum('budget_total');
        $totalTerpakai = $projects->sum('total_terpakai');
        $sisaAnggaran  = $totalAnggaran - $totalTerpakai;
        $overBudget    = $projects->filter(fn($p) => $p->budget_status === 'over')->count();

        $distribusiKategori = Expense::where('status', 'approved')
            ->selectRaw('kategori, SUM(jumlah) as total')
            ->groupBy('kategori')
            ->pluck('total', 'kategori');

        $pengeluaranTerbaru = Expense::with(['project', 'submittedBy'])
            ->latest()->take(10)->get();

        $notifikasi = BudgetNotification::with('project')
            ->where('is_read', false)->latest()->take(5)->get();

        $invoices = Invoice::with('project')
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
        $query = Expense::with(['project', 'submittedBy', 'approvedBy'])->latest();

        if ($request->filled('project_id')) $query->where('project_id', $request->project_id);
        if ($request->filled('kategori'))   $query->where('kategori', $request->kategori);
        if ($request->filled('status'))     $query->where('status', $request->status);

        $expenses = $query->paginate(15);
        $projects = Project::orderBy('nama_project')->get();

        return view('budget.expenses', compact('expenses', 'projects'));
    }

    public function storeExpense(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'project_id'          => 'required|exists:projects,id',
            'nama_pengeluaran'    => 'required|string|max:255',
            'kategori'            => 'required|in:sewa_lokasi,honorarium,peralatan,katering,transportasi,lain_lain',
            'jumlah'              => 'required|numeric|min:1',
            'tanggal_pengeluaran' => 'required|date',
            'keterangan'          => 'nullable|string',
            'bukti_file'          => 'nullable|file|mimes:pdf,jpg,jpeg,png|max:5120',
        ]);

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
        $pending = Expense::with(['project', 'submittedBy'])
            ->where('status', 'pending')
            ->latest()->paginate(15);

        return view('budget.approvals', compact('pending'));
    }

    // ─────────────────────────────────────────
    // INVOICE
    // ─────────────────────────────────────────

    public function invoices(): View
    {
        $invoices = Invoice::with('project')->latest()->paginate(15);
        $projects = Project::orderBy('nama_project')->get();
        return view('budget.invoices', compact('invoices', 'projects'));
    }

    public function storeInvoice(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'project_id'      => 'required|exists:projects,id',
            'nama_vendor'     => 'required|string|max:255',
            'jumlah'          => 'required|numeric|min:1',
            'tanggal_invoice' => 'required|date',
            'jatuh_tempo'     => 'required|date|after_or_equal:tanggal_invoice',
            'keterangan'      => 'nullable|string',
            'file_invoice'    => 'nullable|file|mimes:pdf,jpg,jpeg,png|max:5120',
        ]);

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
        $invoice->update(['status' => 'lunas', 'tanggal_bayar' => now()->toDateString()]);
        return back()->with('success', 'Invoice ditandai lunas.');
    }

    // ─────────────────────────────────────────
    // LAPORAN
    // ─────────────────────────────────────────

    public function laporan(Request $request): View
    {
        $projects        = Project::with(['expenses', 'invoices', 'client'])->get();
        $selectedProject = null;
        $expensesByKategori = [];

        if ($request->filled('project_id')) {
            $selectedProject = Project::with(['expenses', 'invoices', 'client'])
                ->findOrFail($request->project_id);

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
