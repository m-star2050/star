<?php

namespace Packages\Crm\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Task extends Model
{
    use HasFactory, SoftDeletes;

    protected $table = 'crm_tasks';

    protected $fillable = [
        'title',
        'type',
        'priority',
        'due_date',
        'status',
        'assigned_user_id',
        'contact_id',
        'lead_id',
        'notes',
        'attachments',
    ];

    protected $casts = [
        'attachments' => 'array',
        'due_date' => 'date',
    ];

    // Relationships
    public function contact()
    {
        return $this->belongsTo(Contact::class, 'contact_id');
    }

    public function lead()
    {
        return $this->belongsTo(Lead::class, 'lead_id');
    }
}

