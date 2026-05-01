<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Invoice extends Model
{
    use HasFactory;

    protected $fillable = [
        'nomor_invoice','project_id','nama_vendor','jumlah',
        'tanggal_invoice','jatuh_tempo','status',
        'tanggal_bayar','file_invoice','keterangan',
    ];

    protected $casts = [
        'jumlah'          => 'decimal:2',
        'tanggal_invoice' => 'date',
        'jatuh_tempo'     => 'date',
        'tanggal_bayar'   => 'date',
    ];

    public function project()
    {
        return $this->belongsTo(Project::class);
    }

    public function getIsNearDueAttribute(): bool
    {
        if ($this->status === 'lunas') return false;
        $diff = now()->diffInDays($this->jatuh_tempo, false);
        return $diff >= 0 && $diff <= 7;
    }

    public function getIsOverdueAttribute(): bool
    {
        return $this->status === 'belum_bayar' && now()->gt($this->jatuh_tempo);
    }
}
