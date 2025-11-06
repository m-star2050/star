<?php

namespace Packages\Crm\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Contact extends Model
{
    use HasFactory, SoftDeletes;

    protected $table = 'crm_contacts';

    protected $fillable = [
        'name',
        'company',
        'email',
        'phone',
        'assigned_user_id',
        'status',
        'tags',
        'notes',
    ];

    protected $casts = [
        'tags' => 'array',
    ];

    // Relationships
    public function assignedUser()
    {
        return $this->belongsTo(\App\Models\User::class, 'assigned_user_id');
    }
}


