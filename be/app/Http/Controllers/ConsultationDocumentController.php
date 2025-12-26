<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Validator;
use App\Models\ConsultationDocument;

class ConsultationDocumentController extends Controller
{
    /**
     * Upload a document for a consultation
     */
    public function upload(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'consultation_id' => 'required|exists:consultations,id',
            'title' => 'required|string|max:255',
            'document' => 'required|file|mimes:pdf,jpeg,jpg,png,txt,doc,docx|max:10240', // 10MB max
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Validation failed',
                'errors' => $validator->errors()
            ], 422);
        }

        try {
            $file = $request->file('document');
            $consultation_id = $request->input('consultation_id');
            
            // Generate unique filename
            $originalName = $file->getClientOriginalName();
            $extension = $file->getClientOriginalExtension();
            $fileName = time() . '_' . uniqid() . '.' . $extension;
            
            // Store file in storage/app/consultation_documents
            $filePath = $file->storeAs('consultation_documents', $fileName);
            
            // Create database record
            $document = ConsultationDocument::create([
                'consultation_id' => $consultation_id,
                'title' => $request->input('title'),
                'file_name' => $originalName,
                'file_path' => $filePath,
                'file_type' => $extension,
                'file_size' => $file->getSize(),
                'uploaded_by' => $request->input('uploaded_by'),
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Document uploaded successfully',
                'data' => $document
            ], 201);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to upload document: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Get all documents for a consultation
     */
    public function index($consultationId)
    {
        try {
            $documents = ConsultationDocument::where('consultation_id', $consultationId)
                ->with('uploader:id,name')
                ->orderBy('created_at', 'desc')
                ->get();

            return response()->json([
                'success' => true,
                'data' => $documents
            ], 200);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to fetch documents: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Download a document
     */
    public function download($id)
{
    try {
        $document = ConsultationDocument::findOrFail($id);
        
        // The file_path should be: consultation_documents/filename.ext
        $fullPath = storage_path('app/' . $document->file_path);
        
        // Check if file exists
        if (!file_exists($fullPath)) {
            \Log::error('File not found', [
                'file_path' => $document->file_path,
                'full_path' => $fullPath,
                'document_id' => $id
            ]);
            
            return response()->json([
                'success' => false,
                'message' => 'File not found on server'
            ], 404);
        }

        // Return file download
        return response()->download($fullPath, $document->file_name, [
            'Content-Type' => mime_content_type($fullPath)
        ]);

    } catch (\Illuminate\Database\Eloquent\ModelNotFoundException $e) {
        return response()->json([
            'success' => false,
            'message' => 'Document record not found'
        ], 404);
    } catch (\Exception $e) {
        \Log::error('Download error', [
            'error' => $e->getMessage(),
            'trace' => $e->getTraceAsString()
        ]);
        
        return response()->json([
            'success' => false,
            'message' => 'Failed to download document: ' . $e->getMessage()
        ], 500);
    }
}

    /**
     * Delete a document
     */
    public function destroy($id)
    {
        try {
            $document = ConsultationDocument::findOrFail($id);
            
            // Delete file from storage
            if (Storage::exists($document->file_path)) {
                Storage::delete($document->file_path);
            }
            
            // Delete database record
            $document->delete();

            return response()->json([
                'success' => true,
                'message' => 'Document deleted successfully'
            ], 200);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to delete document: ' . $e->getMessage()
            ], 500);
        }
    }
}