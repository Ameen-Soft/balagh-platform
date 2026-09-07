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

    protected function casts(): array
    {
        return [
            'latitude' => 'decimal:7',
            'longitude' => 'decimal:7',
        ];
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
