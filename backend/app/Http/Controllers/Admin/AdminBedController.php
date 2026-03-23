<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Bed;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class AdminBedController extends Controller
{
    public function index(): JsonResponse
    {
        $beds = Bed::with('patient:id,name')
            ->latest()
            ->get()
            ->map(fn ($bed) => [
                'id' => $bed->id,
                'bed_number' => $bed->bed_number,
                'ward' => $bed->ward,
                'floor' => $bed->floor,
                'status' => $bed->status,
                'patient_name' => $bed->patient?->name,
            ]);

        return response()->json(['beds' => $beds]);
    }

    public function store(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'bed_number' => 'required|string|max:255',
            'ward' => 'required|string|max:255',
            'floor' => 'required|integer|min:1',
            'status' => 'required|in:available,occupied,under_maintenance',
        ]);

        $bed = Bed::create($validated);

        return response()->json(['message' => 'Bed created successfully', 'bed' => $bed], 201);
    }

    public function update(Request $request, int $id): JsonResponse
    {
        $bed = Bed::findOrFail($id);

        $validated = $request->validate([
            'bed_number' => 'sometimes|string|max:255',
            'ward' => 'sometimes|string|max:255',
            'floor' => 'sometimes|integer|min:1',
            'status' => 'sometimes|in:available,occupied,under_maintenance',
            'patient_id' => 'sometimes|nullable|exists:users,id',
        ]);

        if (isset($validated['status']) && $validated['status'] === 'available') {
            $validated['patient_id'] = null;
        }

        $bed->update($validated);

        return response()->json(['message' => 'Bed updated successfully']);
    }

    public function destroy(int $id): JsonResponse
    {
        $bed = Bed::findOrFail($id);
        $bed->delete();

        return response()->json(['message' => 'Bed deleted successfully']);
    }

    public function status(): JsonResponse
    {
        $status = Bed::selectRaw('
            COUNT(*) as total,
            SUM(CASE WHEN status = "available" THEN 1 ELSE 0 END) as available,
            SUM(CASE WHEN status = "occupied" THEN 1 ELSE 0 END) as occupied
        ')->first();

        return response()->json([
            'total' => (int) $status->total,
            'available' => (int) $status->available,
            'occupied' => (int) $status->occupied,
        ]);
    }
}
