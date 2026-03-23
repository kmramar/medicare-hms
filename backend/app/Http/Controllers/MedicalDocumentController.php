<?php

namespace App\Http\Controllers;

use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\URL;

class MedicalDocumentController extends Controller
{
    public function upload(Request $request): JsonResponse
    {
        $request->validate([
            'document' => 'required|file|mimes:pdf,jpg,jpeg,png|max:5120',
            'description' => 'nullable|string|max:255',
        ]);

        $user = $request->user();
        
        if ($user->role !== 'patient') {
            return response()->json(['error' => 'Only patients can upload medical documents'], 403);
        }

        $path = $request->file('document')->store('medical-documents', 'public');
        
        $documentData = [
            'path' => $path,
            'original_name' => $request->file('document')->getClientOriginalName(),
            'mime_type' => $request->file('document')->getMimeType(),
            'size' => $request->file('document')->getSize(),
            'description' => $request->input('description', ''),
        ];

        $documents = $user->patientProfile->documents ?? [];
        $documents[] = $documentData;
        
        $user->patientProfile->update(['documents' => $documents]);

        return response()->json([
            'message' => 'Document uploaded successfully',
            'document_url' => URL::to(Storage::url($path)),
            'document' => $documentData,
        ]);
    }

    public function list(Request $request): JsonResponse
    {
        $user = $request->user();
        
        if ($user->role !== 'patient') {
            return response()->json(['error' => 'Only patients can view documents'], 403);
        }

        $documents = $user->patientProfile->documents ?? [];
        
        $documentsWithUrls = array_map(function($doc) {
            $doc['url'] = URL::to(Storage::url($doc['path']));
            return $doc;
        }, $documents);

        return response()->json(['documents' => $documentsWithUrls]);
    }

    public function delete(Request $request, int $index): JsonResponse
    {
        $user = $request->user();
        
        if ($user->role !== 'patient') {
            return response()->json(['error' => 'Only patients can delete documents'], 403);
        }

        $documents = $user->patientProfile->documents ?? [];
        
        if (!isset($documents[$index])) {
            return response()->json(['error' => 'Document not found'], 404);
        }

        Storage::disk('public')->delete($documents[$index]['path']);
        
        array_splice($documents, $index, 1);
        
        $user->patientProfile->update(['documents' => $documents]);

        return response()->json(['message' => 'Document deleted successfully']);
    }
}
