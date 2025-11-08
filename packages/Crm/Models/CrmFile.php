<?php

namespace Packages\Crm\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class CrmFile extends Model
{
    use HasFactory, SoftDeletes;

    protected $table = 'crm_files';

    protected $fillable = [
        'original_name',
        'stored_name',
        'file_name',
        'file_path',
        'path',
        'file_type',
        'file_size',
        'linked_type',
        'linked_id',
        'user_id',
        'uploaded_by', // Keep for backward compatibility
        'description',
    ];

    // Relationships
    public function user()
    {
        return $this->belongsTo(\App\Models\User::class, 'user_id');
    }

    /**
     * Get the linked model (polymorphic relationship)
     */
    public function linked()
    {
        return $this->morphTo();
    }

    /**
     * Get human-readable file size
     */
    public function getFileSizeHumanAttribute()
    {
        $bytes = $this->file_size;
        if ($bytes >= 1073741824) {
            return number_format($bytes / 1073741824, 2) . ' GB';
        } elseif ($bytes >= 1048576) {
            return number_format($bytes / 1048576, 2) . ' MB';
        } elseif ($bytes >= 1024) {
            return number_format($bytes / 1024, 2) . ' KB';
        } else {
            return $bytes . ' bytes';
        }
    }

    /**
     * Check if file is an image
     */
    public function isImage()
    {
        return in_array(strtolower($this->file_type), ['jpg', 'jpeg', 'png', 'gif', 'webp']);
    }

    /**
     * Check if file is a document
     */
    public function isDocument()
    {
        return in_array(strtolower($this->file_type), ['pdf', 'doc', 'docx', 'xls', 'xlsx', 'txt']);
    }

    /**
     * Get icon class based on file type
     */
    public function getIconClass()
    {
        return match(strtolower($this->file_type)) {
            'pdf' => 'text-red-600',
            'doc', 'docx' => 'text-blue-600',
            'xls', 'xlsx' => 'text-green-600',
            'jpg', 'jpeg', 'png', 'gif', 'webp' => 'text-purple-600',
            'zip', 'rar' => 'text-yellow-600',
            default => 'text-gray-600',
        };
    }

    /**
     * Get linked type label
     */
    public function getLinkedTypeLabel()
    {
        return match($this->linked_type) {
            'contact' => 'Contact',
            'lead' => 'Lead',
            'deal' => 'Deal/Pipeline',
            'task' => 'Task',
            default => '-',
        };
    }
}

