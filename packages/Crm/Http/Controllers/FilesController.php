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
        $search = $request->input('search');
        $fileType = $request->input('file_type');
        $linkedType = $request->input('linked_type');
        $perPage = $request->input('per_page', 10);

        $files = CrmFile::query()
            ->when($search, function($q) use ($search) {
                return $q->where('original_name', 'like', "%{$search}%")
                         ->orWhere('description', 'like', "%{$search}%");
            })
            ->when($fileType, function($q) use ($fileType) {
                return $q->where('file_type', $fileType);
            })
            ->when($linkedType, function($q) use ($linkedType) {
                return $q->where('linked_type', $linkedType);
            })
            ->latest()
            ->paginate($perPage);

        $fileTypes = CrmFile::select('file_type')
            ->distinct()
            ->whereNotNull('file_type')
            ->pluck('file_type');

        return view('crm::files.index', compact('files', 'fileTypes', 'search', 'fileType', 'linkedType', 'perPage'));
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
                'file_path' => $filePath,
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
        
        $files = CrmFile::whereIn('id', $ids)->get();
        
        foreach ($files as $file) {
            // Delete physical file
            if (Storage::disk('public')->exists($file->file_path)) {
                Storage::disk('public')->delete($file->file_path);
            }
            
            // Delete database record
            $file->delete();
        }

        return redirect()->route('crm.files.index');
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

