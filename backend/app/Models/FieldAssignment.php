<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class FieldAssignment extends Model
{
    use HasFactory;

    protected $fillable = [
        'complaint_id',
        'worker_id',
        'assigned_by',
        'status',
        'started_at',
        'completed_at',
        'notes',
    ];

    protected function casts(): array
    {
        return [
            'started_at' => 'datetime',
            'completed_at' => 'datetime',
        ];
    }

    public function complaint()
    {
        return $this->belongsTo(Complaint::class);
    }

    public function worker()
    {
        return $this->belongsTo(User::class, 'worker_id');
    }

    public function assignedBy()
    {
        return $this->belongsTo(User::class, 'assigned_by');
    }

    public function evidence()
    {
        return $this->hasMany(ComplaintAttachment::class, 'complaint_id', 'complaint_id')
            ->where(function ($query) {
                $query->where('type', 'after')
                    ->orWhere('type', 'completion_evidence');
            });
    }
}
