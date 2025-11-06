<?php

namespace Packages\Crm\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Packages\Crm\Models\File;

class FilesController extends Controller
{
    public function index()
    {
        return view('crm::files.index');
    }

    public function store(Request $request)
    {
        $request->validate([
            'file' => 'required|file|mimes:pdf,doc,docx,xls,xlsx,jpg,jpeg,png|max:10240',
            'linked_type' => 'nullable|string|in:contact,lead,deal',
            'linked_id' => 'nullable|integer',
            'description' => 'nullable|string|max:1000',
        ]);

        $uploadedFile = $request->file('file');
        $originalName = $uploadedFile->getClientOriginalName();
        $extension = $uploadedFile->getClientOriginalExtension();
        $fileSize = $uploadedFile->getSize();
        $mimeType = $uploadedFile->getMimeType();

        $storedName = 'file_' . time() . '_' . uniqid() . '.' . $extension;
        $filePath = $uploadedFile->storeAs('crm/files', $storedName, 'public');

        $file = File::create([
            'original_name' => $originalName,
            'stored_name' => $storedName,
            'file_path' => $filePath,
            'file_type' => $mimeType,
            'file_size' => $fileSize,
            'linked_type' => $request->input('linked_type'),
            'linked_id' => $request->input('linked_id'),
            'uploaded_by' => auth()->id() ?? 1,
            'description' => $request->input('description'),
        ]);

        if ($request->ajax()) {
            return response()->json([
                'success' => true,
                'message' => 'File uploaded successfully',
                'file' => $file
            ]);
        }

        return redirect()->route('crm.files.index')->with('status', 'File uploaded successfully');
    }

    public function download(File $file)
    {
        if (!Storage::disk('public')->exists($file->file_path)) {
            abort(404, 'File not found');
        }

        return Storage::disk('public')->download($file->file_path, $file->original_name);
    }

    public function preview(File $file)
    {
        if (!Storage::disk('public')->exists($file->file_path)) {
            abort(404, 'File not found');
        }

        $extension = strtolower(pathinfo($file->original_name, PATHINFO_EXTENSION));
        $imageTypes = ['jpg', 'jpeg', 'png', 'gif', 'webp'];
        $pdfTypes = ['pdf'];

        if (in_array($extension, $imageTypes)) {
            return response()->file(Storage::disk('public')->path($file->file_path));
        } elseif (in_array($extension, $pdfTypes)) {
            return response()->file(Storage::disk('public')->path($file->file_path));
        }

        return redirect()->route('crm.files.download', $file);
    }

    public function destroy(Request $request, File $file)
    {
        if (Storage::disk('public')->exists($file->file_path)) {
            Storage::disk('public')->delete($file->file_path);
        }

        $file->delete();

        if ($request->ajax()) {
            return response()->json([
                'success' => true,
                'message' => 'File deleted successfully'
            ]);
        }

        return redirect()->route('crm.files.index')->with('status', 'File deleted successfully');
    }

    public function bulkDelete(Request $request)
    {
        $ids = (array) $request->input('ids', []);

        if (empty($ids)) {
            if ($request->ajax()) {
                return response()->json([
                    'success' => false,
                    'message' => 'No files selected for deletion'
                ], 400);
            }

            return redirect()->route('crm.files.index')->with('error', 'No files selected for deletion');
        }

        $files = File::whereIn('id', $ids)->get();

        foreach ($files as $file) {
            if (Storage::disk('public')->exists($file->file_path)) {
                Storage::disk('public')->delete($file->file_path);
            }
            $file->delete();
        }

        if ($request->ajax()) {
            return response()->json([
                'success' => true,
                'message' => 'Selected files deleted successfully'
            ]);
        }

        return redirect()->route('crm.files.index')->with('status', 'Selected files deleted');
    }

    public function datatable(Request $request)
    {
        $query = File::query();

        if ($search = trim((string) $request->input('search.value'))) {
            $query->where(function ($q) use ($search) {
                $q->where('original_name', 'like', "%{$search}%")
                  ->orWhere('description', 'like', "%{$search}%")
                  ->orWhere('file_type', 'like', "%{$search}%");
            });
        }

        if ($request->filled('file_type')) {
            $query->where('file_type', 'like', '%' . $request->input('file_type') . '%');
        }

        if ($request->filled('linked_type')) {
            $query->where('linked_type', $request->input('linked_type'));
        }

        if ($request->filled('uploaded_from')) {
            $query->whereDate('created_at', '>=', $request->input('uploaded_from'));
        }

        if ($request->filled('uploaded_to')) {
            $query->whereDate('created_at', '<=', $request->input('uploaded_to'));
        }

        $totalRecords = File::count();
        $filteredRecords = $query->count();

        $orderColumn = $request->input('order.0.column', 5);
        $orderDir = $request->input('order.0.dir', 'desc');

        $columns = ['id', 'original_name', 'file_type', 'file_size', 'linked_type', 'created_at', 'uploaded_by'];
        $sortColumn = $columns[$orderColumn] ?? 'created_at';

        $allowedSorts = ['original_name', 'file_type', 'file_size', 'created_at'];
        if (!in_array($sortColumn, $allowedSorts, true)) {
            $sortColumn = 'created_at';
        }

        $orderDir = strtolower($orderDir) === 'asc' ? 'asc' : 'desc';

        $start = $request->input('start', 0);
        $length = $request->input('length', 10);

        $files = $query->orderBy($sortColumn, $orderDir)
            ->skip($start)
            ->take($length)
            ->get();

        $data = $files->map(function ($file) {
            $icon = $file->file_icon;
            $iconHtml = '';
            
            if ($icon === 'image') {
                $iconHtml = '<svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 text-green-500" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z" /></svg>';
            } elseif ($icon === 'pdf') {
                $iconHtml = '<svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 text-red-500" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 21h10a2 2 0 002-2V9.414a1 1 0 00-.293-.707l-5.414-5.414A1 1 0 0012.586 3H7a2 2 0 00-2 2v14a2 2 0 002 2z" /></svg>';
            } elseif ($icon === 'document') {
                $iconHtml = '<svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 text-blue-500" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" /></svg>';
            } elseif ($icon === 'spreadsheet') {
                $iconHtml = '<svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 text-green-600" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 17v-2m3 2v-4m3 4v-6m2 10H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" /></svg>';
            } else {
                $iconHtml = '<svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 text-gray-500" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" /></svg>';
            }

            $linkedInfo = '-';
            if ($file->linked_type && $file->linked_id) {
                $linkedInfo = ucfirst($file->linked_type) . ' #' . $file->linked_id;
            }

            return [
                'id' => $file->id,
                'original_name' => $file->original_name,
                'file_type' => $file->file_type ?? '-',
                'file_size' => $file->formatted_size,
                'linked_type' => $linkedInfo,
                'created_at' => $file->created_at?->format('Y-m-d H:i') ?? '-',
                'uploaded_by' => $file->uploaded_by ? ('User ' . $file->uploaded_by) : '-',
                'icon_html' => $iconHtml,
                'actions_html' => '<div class="flex flex-col sm:flex-row gap-1 justify-center">
                    <a href="' . route('crm.files.preview', $file->id) . '" target="_blank" class="inline-flex items-center gap-1 px-2 sm:px-3 py-1 rounded-lg border border-blue-400 text-blue-600 hover:bg-blue-50 shadow-sm text-xs"><svg xmlns="http://www.w3.org/2000/svg" class="h-3 w-3 sm:h-4 sm:w-4" viewBox="0 0 20 20" fill="currentColor"><path d="M10 12a2 2 0 100-4 2 2 0 000 4z"/><path fill-rule="evenodd" d="M.458 10C1.732 5.943 5.522 3 10 3s8.268 2.943 9.542 7c-1.274 4.057-5.064 7-9.542 7S1.732 14.057.458 10zM14 10a4 4 0 11-8 0 4 4 0 018 0z" clip-rule="evenodd"/></svg><span class="hidden sm:inline">Preview</span></a>
                    <a href="' . route('crm.files.download', $file->id) . '" class="inline-flex items-center gap-1 px-2 sm:px-3 py-1 rounded-lg border border-green-400 text-green-600 hover:bg-green-50 shadow-sm text-xs"><svg xmlns="http://www.w3.org/2000/svg" class="h-3 w-3 sm:h-4 sm:w-4" viewBox="0 0 20 20" fill="currentColor"><path fill-rule="evenodd" d="M3 17a1 1 0 011-1h12a1 1 0 110 2H4a1 1 0 01-1-1zm3.293-7.707a1 1 0 011.414 0L9 10.586V3a1 1 0 112 0v7.586l1.293-1.293a1 1 0 111.414 1.414l-3 3a1 1 0 01-1.414 0l-3-3a1 1 0 010-1.414z" clip-rule="evenodd"/></svg><span class="hidden sm:inline">Download</span></a>
                    <button type="button" class="inline-flex items-center gap-1 px-2 sm:px-3 py-1 rounded-lg border border-red-400 text-red-600 hover:bg-red-50 shadow-sm text-xs delete-btn" data-id="' . $file->id . '" data-name="' . htmlspecialchars($file->original_name, ENT_QUOTES) . '"><svg xmlns="http://www.w3.org/2000/svg" class="h-3 w-3 sm:h-4 sm:w-4" viewBox="0 0 20 20" fill="currentColor"><path fill-rule="evenodd" d="M9 2a1 1 0 00-.894.553L7.382 4H4a1 1 0 000 2v10a2 2 0 002 2h8a2 2 0 002-2V6a1 1 0 100-2h-3.382l-.724-1.447A1 1 0 0011 2H9zM7 8a1 1 0 012 0v6a1 1 0 11-2 0V8zm5-1a1 1 0 00-1 1v6a1 1 0 102 0V8a1 1 0 00-1-1z" clip-rule="evenodd"/></svg><span class="hidden sm:inline">Delete</span></button>
                </div>',
            ];
        });

        return response()->json([
            'draw' => (int) $request->input('draw'),
            'recordsTotal' => $totalRecords,
            'recordsFiltered' => $filteredRecords,
            'data' => $data,
        ]);
    }
}

