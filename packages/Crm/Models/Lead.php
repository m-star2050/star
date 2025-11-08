<?php

namespace Packages\Crm\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Lead extends Model
{
    use HasFactory, SoftDeletes;

    protected $table = 'crm_leads';

    protected $fillable = [
        'name',
        'email',
        'company',
        'source',
        'stage',
        'user_id',
        'assigned_user_id', // Keep for backward compatibility
        'lead_score',
        'tags',
        'notes',
    ];

    protected $casts = [
        'tags' => 'array',
    ];

    // Relationships
    public function user()
    {
        return $this->belongsTo(\App\Models\User::class, 'user_id');
    }

    public function assignedUser()
    {
        return $this->belongsTo(\App\Models\User::class, 'assigned_user_id');
    }
}
