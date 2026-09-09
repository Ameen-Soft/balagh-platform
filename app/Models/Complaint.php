<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Complaint extends Model
{
    use HasFactory;

    protected $fillable = [
        'citizen_id',
        'category_id',
        'current_department_id',
        'duplicate_of_id',
        'title',
        'description',
        'status',
        'priority',
        'latitude',
        'longitude',
    ];

    protected $appends = [
        'complaint_number',
        'priority_arabic',
        'status_arabic',
    ];

    protected function casts(): array
    {
        return [
            'latitude' => 'decimal:7',
            'longitude' => 'decimal:7',
        ];
    }

    public function getComplaintNumberAttribute(): string
    {
        return 'CMP-' . str_pad((string) $this->id, 6, '0', STR_PAD_LEFT);
    }

    public function getPriorityArabicAttribute(): string
    {
        return match ($this->priority) {
            'urgent' => 'طارئ',
            'high' => 'عالي',
            'medium' => 'متوسط',
            'low' => 'منخفض',
            default => $this->priority ?? 'عادي',
        };
    }

    public function getStatusArabicAttribute(): string
    {
        return match ($this->status) {
            'new', 'submitted' => 'جديد وارد',
            'under_review' => 'قيد المراجعة',
            'assigned' => 'مسند للميدان',
            'in_progress' => 'قيد التنفيذ',
            'resolved' => 'تم الإنجاز',
            'rejected' => 'مرفوض',
            'closed' => 'مغلق',
            default => $this->status ?? 'غير محدد',
        };
    }

    public function getPriorityBadgeClassAttribute(): string
    {
        return match ($this->priority) {
            'urgent' => 'bg-red-100 text-red-800 border border-red-200',
            'high' => 'bg-amber-100 text-amber-800 border border-amber-200',
            'medium' => 'bg-blue-100 text-blue-800 border border-blue-200',
            'low' => 'bg-slate-100 text-slate-700 border border-slate-200',
            default => 'bg-slate-100 text-slate-700 border border-slate-200',
        };
    }

    public function citizen()
    {
        return $this->belongsTo(User::class, 'citizen_id');
    }

    public function category()
    {
        return $this->belongsTo(Category::class, 'category_id');
    }

    public function currentDepartment()
    {
        return $this->belongsTo(Department::class, 'current_department_id');
    }

    public function ministry()
    {
        return $this->hasOneThrough(Ministry::class, Department::class, 'id', 'id', 'current_department_id', 'ministry_id');
    }

    public function duplicateOf()
    {
        return $this->belongsTo(Complaint::class, 'duplicate_of_id');
    }

    public function duplicates()
    {
        return $this->hasMany(Complaint::class, 'duplicate_of_id');
    }

    public function attachments()
    {
        return $this->hasMany(ComplaintAttachment::class, 'complaint_id');
    }

    public function timeline()
    {
        return $this->hasMany(ComplaintTimeline::class, 'complaint_id')->orderBy('created_at', 'asc');
    }

    public function timelines()
    {
        return $this->timeline();
    }

    public function transfers()
    {
        return $this->hasMany(ComplaintTransfer::class, 'complaint_id')->orderBy('created_at', 'asc');
    }

    public function fieldAssignments()
    {
        return $this->hasMany(FieldAssignment::class, 'complaint_id');
    }

    public function activeFieldAssignment()
    {
        return $this->hasOne(FieldAssignment::class, 'complaint_id')
            ->whereIn('status', ['pending', 'accepted', 'in_progress'])
            ->latestOfMany();
    }
}
