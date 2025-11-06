<?php

namespace Packages\Crm\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class File extends Model
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
        'uploaded_by',
        'description',
    ];

    public function getFileIconAttribute()
    {
        $extension = strtolower(pathinfo($this->original_name, PATHINFO_EXTENSION));
        
        $imageTypes = ['jpg', 'jpeg', 'png', 'gif', 'webp'];
        $pdfTypes = ['pdf'];
        $docTypes = ['doc', 'docx'];
        $xlsTypes = ['xls', 'xlsx'];
        
        if (in_array($extension, $imageTypes)) {
            return 'image';
        } elseif (in_array($extension, $pdfTypes)) {
            return 'pdf';
        } elseif (in_array($extension, $docTypes)) {
            return 'document';
        } elseif (in_array($extension, $xlsTypes)) {
            return 'spreadsheet';
        }
        
        return 'file';
    }

    public function getFormattedSizeAttribute()
    {
        if (!$this->file_size) {
            return '-';
        }
        
        $bytes = $this->file_size;
        $units = ['B', 'KB', 'MB', 'GB'];
        $i = 0;
        
        while ($bytes >= 1024 && $i < count($units) - 1) {
            $bytes /= 1024;
            $i++;
        }
        
        return round($bytes, 2) . ' ' . $units[$i];
    }
}

