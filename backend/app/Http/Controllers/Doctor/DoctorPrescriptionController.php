<?php

namespace App\Http\Controllers\Doctor;

use App\Http\Controllers\Controller;
use App\Models\Prescription;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class DoctorPrescriptionController extends Controller
{
    public function index(): JsonResponse
    {
        $doctor = request()->user();

        $prescriptions = Prescription::where('doctor_id', $doctor->id)
            ->with('patient:id,name')
            ->latest()
            ->get()
            ->map(fn ($pres) => [
                'id' => $pres->id,
                'patient_name' => $pres->patient->name,
                'diagnosis' => $pres->diagnosis,
                'created_at' => $pres->created_at->toDateTimeString(),
            ]);

        return response()->json(['prescriptions' => $prescriptions]);
    }

    public function store(Request $request): JsonResponse
    {
        $doctor = $request->user();

        $validated = $request->validate([
            'appointment_id' => 'required|exists:appointments,id',
            'patient_id' => 'required|exists:users,id',
            'diagnosis' => 'required|string',
            'medicines' => 'required|array|min:1',
            'medicines.*.name' => 'required|string',
            'medicines.*.dosage' => 'required|string',
            'medicines.*.duration' => 'required|string',
            'instructions' => 'nullable|string',
            'follow_up_date' => 'nullable|date',
        ]);

        $prescription = Prescription::create([
            'doctor_id' => $doctor->id,
            'patient_id' => $validated['patient_id'],
            'appointment_id' => $validated['appointment_id'],
            'diagnosis' => $validated['diagnosis'],
            'medicines' => $validated['medicines'],
            'instructions' => $validated['instructions'] ?? null,
            'follow_up_date' => $validated['follow_up_date'] ?? null,
        ]);

        return response()->json(['message' => 'Prescription created successfully'], 201);
    }

    public function show(int $id): JsonResponse
    {
        $doctor = request()->user();

        $prescription = Prescription::where('doctor_id', $doctor->id)
            ->with('patient:id,name')
            ->findOrFail($id);

        return response()->json([
            'prescription' => [
                'id' => $prescription->id,
                'patient_name' => $prescription->patient->name,
                'diagnosis' => $prescription->diagnosis,
                'medicines' => $prescription->medicines,
                'instructions' => $prescription->instructions,
                'follow_up_date' => $prescription->follow_up_date?->toDateString(),
                'created_at' => $prescription->created_at->toDateTimeString(),
            ],
        ]);
    }
}
