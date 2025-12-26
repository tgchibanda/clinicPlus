<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Validator;
use App\Models\ConsultationDocument;

class ConsultationDocumentController extends Controller
{
    /**
     * Get all documents for a consultation
     */
    public function index($consultationId)
    {
        try {
            $documents = ConsultationDocument::where('consultation_id', $consultationId)
                ->orderBy('created_at', 'desc')
                ->get();

            return response()->json([
                'success' => true,
                'message' => 'Documents retrieved successfully',
                'data' => $documents
            ], 200);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to retrieve documents: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Upload a new document with unique timestamp-based filename
     */
    public function upload(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'consultation_id' => 'required|exists:consultations,id',
            'title' => 'required|string|max:255',
            'document' => 'required|file|mimes:pdf,jpeg,jpg,png,txt,doc,docx|max:10240', // 10MB max
            'uploaded_by' => 'nullable|exists:users,id',
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

            // Get original file info
            $originalName = $file->getClientOriginalName();
            $extension = $file->getClientOriginalExtension();

            // Sanitize original filename (remove extension and special chars)
            $sanitizedBaseName = preg_replace('/[^A-Za-z0-9\-_]/', '_', pathinfo($originalName, PATHINFO_FILENAME));
            $sanitizedBaseName = substr($sanitizedBaseName, 0, 50); // Limit length

            // Generate unique filename with multiple components for maximum uniqueness
            // Format: {sanitized_name}_{consultation_id}_{timestamp}_{uuid}.{extension}
            // Example: Medical_Report_123_1703612345_a1b2c3d4e5f6.pdf
            $uuid = substr(md5(uniqid(rand(), true)), 0, 12); // 12-char random hash
            $timestamp = time();

            $uniqueFileName = sprintf(
                '%s_%s_%s_%s.%s',
                $sanitizedBaseName,
                $consultation_id,
                $timestamp,
                $uuid,
                $extension
            );

            // Ensure filename doesn't exceed filesystem limits (255 chars is common)
            if (strlen($uniqueFileName) > 200) {
                // If too long, use shorter version without original name
                $uniqueFileName = sprintf(
                    'doc_%s_%s_%s.%s',
                    $consultation_id,
                    $timestamp,
                    $uuid,
                    $extension
                );
            }

            // Double-check for uniqueness (extremely rare collision scenario)
            $counter = 1;
            $baseUniqueFileName = $uniqueFileName;
            while (Storage::disk('public')->exists('consultation_documents/' . $uniqueFileName)) {
                $nameWithoutExt = pathinfo($baseUniqueFileName, PATHINFO_FILENAME);
                $uniqueFileName = $nameWithoutExt . '_' . $counter . '.' . $extension;
                $counter++;
            }

            // Store file in storage/app/public/consultation_documents
            $filePath = $file->storeAs('consultation_documents', $uniqueFileName, 'public');

            // Verify file was stored successfully
            if (!$filePath) {
                throw new \Exception('Failed to store file on disk');
            }

            // Create database record
            $document = ConsultationDocument::create([
                'consultation_id' => $consultation_id,
                'title' => $request->input('title'),
                'file_name' => $originalName, // Keep original name for display
                'file_path' => $filePath, // Store path with unique name
                'file_type' => strtolower($extension),
                'file_size' => $file->getSize(),
                'uploaded_by' => $request->input('uploaded_by'),
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Document uploaded successfully',
                'data' => $document
            ], 201);
        } catch (\Exception $e) {
            // Clean up file if database insert fails
            if (isset($filePath) && Storage::disk('public')->exists($filePath)) {
                Storage::disk('public')->delete($filePath);
            }

            \Log::error('Document upload failed', [
                'error' => $e->getMessage(),
                'consultation_id' => $request->input('consultation_id'),
                'file_name' => $request->file('document') ? $request->file('document')->getClientOriginalName() : null
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Failed to upload document: ' . $e->getMessage()
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

            // Get file from storage
            $filePath = storage_path('app/public/' . $document->file_path);

            // Check if file exists
            if (!file_exists($filePath)) {
                return response()->json([
                    'success' => false,
                    'message' => 'File not found'
                ], 404);
            }

            // Return file download with original filename
            return response()->download($filePath, $document->file_name);
        } catch (\Exception $e) {
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
            if (Storage::disk('public')->exists($document->file_path)) {
                Storage::disk('public')->delete($document->file_path);
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
