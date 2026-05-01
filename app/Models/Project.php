<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Project extends Model
{
    use HasFactory;

    protected $fillable = [
        'kode_project','nama_project','client_id','pic_id',
        'status','tipe_iklan','tanggal_mulai','tanggal_deadline',
        'budget_total','deskripsi',
    ];

    protected $casts = [
        'tanggal_mulai'    => 'date',
        'tanggal_deadline' => 'date',
        'budget_total'     => 'decimal:2',
    ];

    public function client()
    {
        return $this->belongsTo(Client::class);
    }

    public function pic()
    {
        return $this->belongsTo(User::class, 'pic_id');
    }

    public function expenses()
    {
        return $this->hasMany(Expense::class);
    }

    public function invoices()
    {
        return $this->hasMany(Invoice::class);
    }

    public function notifications()
    {
        return $this->hasMany(BudgetNotification::class);
    }

    public function crews()
    {
    return $this->hasMany(ProjectCrew::class);
    }

    // Total pengeluaran approved saja
    public function getTotalTerpakaiAttribute(): float
    {
        return (float) $this->expenses()->where('status', 'approved')->sum('jumlah');
    }

    // Persentase pemakaian budget
    public function getPorsentasePemakainanAttribute(): float
    {
        if ((float)$this->budget_total == 0) return 0;
        return round(($this->total_terpakai / (float)$this->budget_total) * 100, 1);
    }

    // Sisa anggaran
    public function getSisaAnggaranAttribute(): float
    {
        return (float)$this->budget_total - $this->total_terpakai;
    }

    // Status budget: over / warning / aman
    public function getBudgetStatusAttribute(): string
    {
        $pct = $this->porsentase_pemakainan;
        if ($pct > 100) return 'over';
        if ($pct >= 80)  return 'warning';
        return 'aman';
    }
}
