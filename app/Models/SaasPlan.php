<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class SaasPlan extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'slug',
        'code',
        'monthly_price',
        'annual_price',
        'property_limit',
        'featured_limit',
        'staff_limit',
        'duration_days',
        'features',
        'is_active',
    ];

    protected $casts = [
        'features' => 'array',
        'is_active' => 'boolean',
        'monthly_price' => 'decimal:2',
        'annual_price' => 'decimal:2',
    ];

    public function tenants()
    {
        return $this->hasMany(SaasTenant::class, 'plan_id');
    }

    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }
}

