<?php

use App\Http\Controllers\AuthController;
use App\Http\Controllers\BudgetController;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\CrewController;

// ── AUTH ─────────────────────────────────────────────────
Route::get('/',        fn() => redirect()->route('login'));
Route::get('/login',   [AuthController::class, 'loginForm'])->name('login');
Route::post('/login',  [AuthController::class, 'login'])->name('login.post');
Route::post('/logout', [AuthController::class, 'logout'])->name('logout');

// ── BUDGET (perlu login) ──────────────────────────────────
Route::prefix('budget')->name('budget.')->middleware(['auth'])->group(function () {

    Route::get('/',  [BudgetController::class, 'index'])->name('index');

    // Pengeluaran
    Route::get('/expenses',                         [BudgetController::class, 'expenses'])     ->name('expenses');
    Route::post('/expenses',                        [BudgetController::class, 'storeExpense']) ->name('expenses.store');
    Route::patch('/expenses/{expense}/approve',     [BudgetController::class, 'approveExpense'])->name('expenses.approve');
    Route::patch('/expenses/{expense}/reject',      [BudgetController::class, 'rejectExpense'])->name('expenses.reject');

    // Approval
    Route::get('/approvals', [BudgetController::class, 'approvals'])->name('approvals');

    // Invoice
    Route::get('/invoices',                     [BudgetController::class, 'invoices'])    ->name('invoices');
    Route::post('/invoices',                    [BudgetController::class, 'storeInvoice'])->name('invoices.store');
    Route::patch('/invoices/{invoice}/bayar',   [BudgetController::class, 'bayarInvoice'])->name('invoices.bayar');

    // Laporan
    Route::get('/laporan', [BudgetController::class, 'laporan'])->name('laporan');

    // Hire Major Crew
    Route::get('/hire-crew', function() {
        return view('budget.hire-crew', ['projects' => \App\Models\Project::all()]);
    })->name('hire-crew-page');

    Route::post('/hire-crew/{projectId}', [CrewController::class, 'hireForProject'])->name('hire-crew');
});
