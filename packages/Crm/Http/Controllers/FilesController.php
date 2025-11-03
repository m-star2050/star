<?php

namespace Packages\Crm\Http\Controllers;

use App\Http\Controllers\Controller;
use Packages\Crm\Models\CrmFile;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class FilesController extends Controller
{
    /**
     * Display file listing
     */
    public function index(Request $request)
    {
        // Validate and set per_page with proper bounds
        $perPage = (int) $request->input('per_page', 10);
        if ($perPage < 1) $perPage = 10;
        if ($perPage > 100) $perPage = 100;
        
        $search = trim((string) $request->input('search', ''));
        $fileType = $request->input('file_type');
        $linkedType = $request->input('linked_type');
        $sort = $request->input('sort', 'created_at');
        $direction = $request->input('direction', 'desc');

        $files = CrmFile::query()
            ->when(!empty($search), function($q) use ($search) {
                return $q->where('original_name', 'like', "%{$search}%")
                         ->orWhere('description', 'like', "%{$search}%");
            })
            ->when(!empty($fileType), function($q) use ($fileType) {
                return $q->where('file_type', $fileType);
            })
            ->when(!empty($linkedType), function($q) use ($linkedType) {
                return $q->where('linked_type', $linkedType);
            });

        $allowedSorts = ['original_name', 'file_type', 'file_size', 'linked_type', 'created_at', 'uploaded_by'];
        if (!in_array($sort, $allowedSorts, true)) {
            $sort = 'created_at';
        }
        $direction = strtolower($direction) === 'asc' ? 'asc' : 'desc';

        $files = $files->orderBy($sort, $direction)
            ->paginate($perPage)
            ->withQueryString();

        $fileTypes = CrmFile::select('file_type')
            ->distinct()
            ->whereNotNull('file_type')
            ->pluck('file_type');

        return view('crm::files.index', compact('files', 'fileTypes', 'search', 'fileType', 'linkedType', 'perPage', 'sort', 'direction'));
    }

    /**
     * Upload new file
     */
    public function store(Request $request)
    {
        $request->validate([
            'file' => 'required|file|max:10240', // 10MB max
            'linked_type' => 'nullable|in:contact,lead,deal,task',
            'linked_id' => 'nullable|integer',
            'description' => 'nullable|string|max:500',
        ]);

        if ($request->hasFile('file')) {
            $file = $request->file('file');
            $originalName = $file->getClientOriginalName();
            $extension = $file->getClientOriginalExtension();
            
            // Generate unique filename
            $storedName = time() . '_' . Str::random(10) . '.' . $extension;
            
            // Store file
            $filePath = $file->storeAs('crm_files', $storedName, 'public');

            // Create database record
            CrmFile::create([
                'original_name' => $originalName,
                'stored_name' => $storedName,
                'file_name' => $originalName, // Add file_name field for database compatibility
                'file_path' => $filePath,
                'path' => $filePath, // Add path field for database compatibility (legacy column)
                'file_type' => $extension,
                'file_size' => $file->getSize(),
                'linked_type' => $request->input('linked_type'),
                'linked_id' => $request->input('linked_id'),
                'uploaded_by' => auth()->id() ?? 1,
                'description' => $request->input('description'),
            ]);

            return redirect()->route('crm.files.index')->with('success', 'File uploaded successfully');
        }

        return redirect()->back()->with('error', 'No file uploaded');
    }

    /**
     * Download file
     */
    public function download(CrmFile $file)
    {
        $filePath = storage_path('app/public/' . $file->file_path);

        if (file_exists($filePath)) {
            return response()->download($filePath, $file->original_name);
        }

        return redirect()->back()->with('error', 'File not found');
    }

    /**
     * Delete file
     */
    public function destroy(CrmFile $file)
    {
        // Delete physical file
        if (Storage::disk('public')->exists($file->file_path)) {
            Storage::disk('public')->delete($file->file_path);
        }

        // Delete database record
        $file->delete();

        return redirect()->route('crm.files.index')->with('success', 'File deleted successfully');
    }

    /**
     * Bulk delete files
     */
    public function bulkDelete(Request $request)
    {
        $ids = (array) $request->input('ids', []);
        
        if (empty($ids)) {
            return redirect()->route('crm.files.index')->with('error', 'No files selected');
        }
        
        $files = CrmFile::whereIn('id', $ids)->get();
        
        foreach ($files as $file) {
            // Delete physical file
            if (Storage::disk('public')->exists($file->file_path)) {
                Storage::disk('public')->delete($file->file_path);
            }
            
            // Delete database record
            $file->delete();
        }

        return redirect()->route('crm.files.index')->with('success', count($ids) . ' file(s) deleted successfully');
    }

    /**
     * Preview file (for images/PDFs)
     */
    public function preview(CrmFile $file)
    {
        $filePath = storage_path('app/public/' . $file->file_path);

        if (file_exists($filePath)) {
            $mimeType = mime_content_type($filePath);
            return response()->file($filePath, ['Content-Type' => $mimeType]);
        }

        abort(404, 'File not found');
    }
}

