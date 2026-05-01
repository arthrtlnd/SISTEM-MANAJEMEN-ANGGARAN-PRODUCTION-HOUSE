<?php

namespace App\Traits;

use App\Models\Project;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\Auth;

/**
 * Trait untuk handle role-based access control
 * Gunakan ini di Controller untuk filter data berdasarkan role user
 */
trait AccessControlTrait
{
    /**
     * Filter projects berdasarkan role user
     * - ADMIN: Lihat semua project
     * - PRODUSER: Lihat hanya project yang dia kelola
     * - CREW: Lihat project yang di-assign
     */
    protected function getProjectsForUser()
    {
        $user = Auth::user();

        if ($user->isAdmin()) {
            // Admin lihat semua
            return Project::with(['expenses', 'client', 'crews'])->get();
        } else if ($user->isProduser()) {
            // Produser hanya lihat project yang mereka kelola
            return Project::where('pic_id', $user->id)
                ->with(['expenses', 'client', 'crews'])
                ->get();
        } else if ($user->isCrew()) {
            // Crew lihat project yang di-assign
            return $user->assignedProjects()->with(['expenses', 'client'])->get();
        }

        return collect();
    }

    /**
     * Filter projects untuk hire crew
     * - ADMIN: Semua project
     * - PRODUSER: Project mereka saja
     */
    protected function getProjectsForHiring()
    {
        $user = Auth::user();

        if ($user->isAdmin()) {
            return Project::all();
        } else if ($user->isProduser()) {
            return Project::where('pic_id', $user->id)->get();
        }

        abort(403, 'Anda tidak memiliki akses untuk hire crew.');
    }

    /**
     * Cek apakah user bisa manage project tertentu
     */
    protected function canManageProject(Project $project): bool
    {
        $user = Auth::user();

        if ($user->isAdmin()) {
            return true;
        }

        if ($user->isProduser() && $project->pic_id === $user->id) {
            return true;
        }

        return false;
    }

    /**
     * Scope untuk filter expenses berdasarkan role
     * - ADMIN: Semua expense
     * - PRODUSER: Expense dari project mereka
     * - CREW: Expense yang mereka submit + yang di-assign
     */
    protected function scopeExpensesForUser(Builder $query)
    {
        $user = Auth::user();

        if ($user->isAdmin()) {
            // Admin lihat semua
            return $query;
        } else if ($user->isProduser()) {
            // Produser lihat expense dari project mereka
            return $query->whereHas('project', fn($q) => $q->where('pic_id', $user->id));
        } else if ($user->isCrew()) {
            // Crew lihat expense yang mereka submit
            return $query->where('submitted_by', $user->id);
        }

        return $query->whereRaw('1=0'); // Return kosong
    }

    /**
     * Cek apakah pengeluaran bisa di-approve oleh user
     * - ADMIN: Bisa approve semua (>= Rp 1jt)
     * - PRODUSER: Bisa approve expense crew mereka (< Rp 1jt)
     * - CREW: TIDAK bisa approve
     */
    protected function canApproveExpense($expense): bool
    {
        $user = Auth::user();
        $amount = $expense->jumlah;

        if ($user->isAdmin()) {
            // Admin approve semua pengeluaran besar
            return $amount >= 1000000;
        }

        if ($user->isProduser()) {
            // Produser approve pengeluaran crew mereka (< Rp 1jt)
            $isTheirProject = $expense->project->pic_id === $user->id;
            return $isTheirProject && $amount < 1000000;
        }

        return false;
    }

    /**
     * Get approval threshold untuk user
     * - ADMIN: Rp 1.000.000 ke atas
     * - PRODUSER: Rp 1.000.000 ke bawah
     * - CREW: Tidak bisa approve
     */
    protected function getApprovalThreshold(): int
    {
        $user = Auth::user();

        if ($user->isAdmin()) {
            return 1000000; // Bisa approve >= Rp 1jt
        }

        if ($user->isProduser()) {
            return 1000000; // Bisa approve < Rp 1jt
        }

        return 0; // Tidak bisa approve
    }
}
