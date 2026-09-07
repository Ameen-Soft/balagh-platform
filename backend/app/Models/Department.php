<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Department extends Model
{
    use HasFactory;

    protected $fillable = [
        'ministry_id',
        'name',
        'description',
        'is_active',
    ];

    protected function casts(): array
    {
        return [
            'is_active' => 'boolean',
        ];
    }

    public function ministry()
    {
        return $this->belongsTo(Ministry::class);
    }

    public function categories()
    {
        return $this->hasMany(Category::class);
    }

    public function users()
    {
        return $this->hasMany(User::class);
    }

    public function complaints()
    {
        return $this->hasMany(Complaint::class, 'current_department_id');
    }

    public function projects()
    {
        return $this->hasMany(Project::class);
    }

    public function incomingTransfers()
    {
        return $this->hasMany(ComplaintTransfer::class, 'to_department_id');
    }

    public function outgoingTransfers()
    {
        return $this->hasMany(ComplaintTransfer::class, 'from_department_id');
    }
}
