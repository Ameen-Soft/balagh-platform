<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ComplaintAttachment extends Model
{
    use HasFactory;

    const UPDATED_AT = null;

    protected $fillable = [
        'complaint_id',
        'file_path',
        'file_type',
        'captured_latitude',
        'captured_longitude',
        'uploaded_by',
        'type',
    ];

    protected function casts(): array
    {
        return [
            'captured_latitude' => 'decimal:7',
            'captured_longitude' => 'decimal:7',
        ];
    }

    public function complaint()
    {
        return $this->belongsTo(Complaint::class);
    }

    public function uploader()
    {
        return $this->belongsTo(User::class, 'uploaded_by');
    }
}
