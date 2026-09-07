<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ComplaintTimeline extends Model
{
    use HasFactory;

    protected $table = 'complaint_timeline';

    const UPDATED_AT = null;

    protected $fillable = [
        'complaint_id',
        'event_type',
        'description',
        'old_value',
        'new_value',
        'performed_by',
    ];

    public function complaint()
    {
        return $this->belongsTo(Complaint::class);
    }

    public function performer()
    {
        return $this->belongsTo(User::class, 'performed_by');
    }
}
