<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ComplaintTransfer extends Model
{
    use HasFactory;

    const UPDATED_AT = null;

    protected $fillable = [
        'complaint_id',
        'from_department_id',
        'to_department_id',
        'reason',
        'transferred_by',
    ];

    public function complaint()
    {
        return $this->belongsTo(Complaint::class);
    }

    public function fromDepartment()
    {
        return $this->belongsTo(Department::class, 'from_department_id');
    }

    public function toDepartment()
    {
        return $this->belongsTo(Department::class, 'to_department_id');
    }

    public function transferredBy()
    {
        return $this->belongsTo(User::class, 'transferred_by');
    }
}
