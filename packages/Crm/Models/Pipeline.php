<?php

namespace Packages\Crm\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Pipeline extends Model
{
    use HasFactory, SoftDeletes;

    protected $table = 'crm_pipelines';

    protected $fillable = [
        'deal_name',
        'stage',
        'value',
        'owner_user_id',
        'close_date',
        'probability',
        'contact_id',
        'company',
        'notes',
    ];

    protected $casts = [
        'close_date' => 'date',
        'value' => 'decimal:2',
        'probability' => 'integer',
    ];

    // Relationships
    public function contact()
    {
        return $this->belongsTo(Contact::class, 'contact_id');
    }

    // Helper methods
    public function getStageColorClass()
    {
        return match($this->stage) {
            'prospect' => 'text-gray-700',
            'negotiation' => 'text-blue-700',
            'proposal' => 'text-yellow-700',
            'closed_won' => 'text-green-700',
            'closed_lost' => 'text-red-700',
            default => 'text-gray-700',
        };
    }

    public function getStageLabel()
    {
        return match($this->stage) {
            'prospect' => 'Prospect',
            'negotiation' => 'Negotiation',
            'proposal' => 'Proposal',
            'closed_won' => 'Closed Won',
            'closed_lost' => 'Closed Lost',
            default => ucfirst($this->stage),
        };
    }
}

