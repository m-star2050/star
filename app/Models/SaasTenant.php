<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class SaasTenant extends Model
{
    use HasFactory;

    protected $fillable = [
        'plan_id',
        'name',
        'slug',
        'code',
        'database',
        'path',
        'status',
        'activated_at',
        'expires_at',
        'suspended_at',
        'limits',
        'settings',
        'billing',
    ];

    protected $casts = [
        'limits' => 'array',
        'settings' => 'array',
        'billing' => 'array',
        'activated_at' => 'datetime',
        'expires_at' => 'datetime',
        'suspended_at' => 'datetime',
    ];

    public function plan()
    {
        return $this->belongsTo(SaasPlan::class, 'plan_id');
    }
}

