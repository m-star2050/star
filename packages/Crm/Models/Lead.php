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
        'assigned_user_id',
        'lead_score',
        'tags',
        'notes',
    ];

    protected $casts = [
        'tags' => 'array',
    ];
}
