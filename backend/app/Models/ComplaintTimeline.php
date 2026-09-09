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

    protected $appends = [
        'action',
        'notes',
    ];

    public function complaint()
    {
        return $this->belongsTo(Complaint::class);
    }

    public function actor()
    {
        return $this->belongsTo(User::class, 'performed_by');
    }

    public function performer()
    {
        return $this->actor();
    }

    public function getActionAttribute(): string
    {
        return match ($this->event_type) {
            'created' => 'إنشاء البلاغ',
            'verified' => 'التحقق والمطابقة',
            'assigned' => 'إسناد للميدان',
            'transferred' => 'إحالة لجهة أخرى',
            'status_changed' => 'تغيير الحالة',
            'evidence_uploaded' => 'رفع صور توثيقية',
            'resolved' => 'معالجة البلاغ',
            'closed' => 'إغلاق نهائي للبلاغ',
            'reopened' => 'إعادة فتح البلاغ',
            'rejected' => 'رفض البلاغ',
            default => (string) ($this->description ?? $this->event_type ?? 'إجراء نظام'),
        };
    }

    public function getNotesAttribute(): ?string
    {
        return $this->description;
    }
}
