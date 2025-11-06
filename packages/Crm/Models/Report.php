<?php

namespace Packages\Crm\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Report extends Model
{
    use HasFactory, SoftDeletes;

    protected $table = 'crm_reports';

    protected $fillable = [
        'type',
        'filters',
        'data',
        'generated_at',
        'created_by',
    ];

    protected $casts = [
        'filters' => 'array',
        'data' => 'array',
        'generated_at' => 'datetime',
    ];
}

