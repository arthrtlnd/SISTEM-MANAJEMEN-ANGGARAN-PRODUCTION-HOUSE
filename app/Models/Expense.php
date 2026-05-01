<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Expense extends Model
{
    use HasFactory;

    protected $fillable = [
        'project_id','submitted_by','approved_by',
        'nama_pengeluaran','kategori','jumlah',
        'keterangan','status','tanggal_pengeluaran',
        'bukti_file','approved_at',
    ];

    protected $casts = [
        'jumlah'              => 'decimal:2',
        'tanggal_pengeluaran' => 'date',
        'approved_at'         => 'datetime',
    ];

    public static array $kategoriLabel = [
        'sewa_lokasi'  => 'Sewa Lokasi/Studio',
        'honorarium'   => 'Honorarium Talent',
        'peralatan'    => 'Peralatan & Sewa Alat',
        'katering'     => 'Katering',
        'transportasi' => 'Transportasi',
        'lain_lain'    => 'Lain-lain',
    ];

    public function project()
    {
        return $this->belongsTo(Project::class);
    }

    public function submittedBy()
    {
        return $this->belongsTo(User::class, 'submitted_by');
    }

    public function approvedBy()
    {
        return $this->belongsTo(User::class, 'approved_by');
    }

    public function getKategoriLabelAttribute(): string
    {
        return self::$kategoriLabel[$this->kategori] ?? $this->kategori;
    }
}
