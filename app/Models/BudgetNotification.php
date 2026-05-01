<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class BudgetNotification extends Model
{
    use HasFactory;

    protected $table = 'budget_notifications';

    protected $fillable = ['project_id','tipe','pesan','is_read'];

    protected $casts = ['is_read' => 'boolean'];

    public function project()
    {
        return $this->belongsTo(Project::class);
    }
}
