<?php

namespace App\Http\Controllers\Patient;

use App\Http\Controllers\Controller;
use App\Models\Prescription;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class PatientPrescriptionController extends Controller
{
    public function index()
    {
        $patient = Auth::user();

        $prescriptions = Prescription::with(['appointment.doctorProfile.user', 'medicines'])
            ->whereHas('appointment', function ($query) use ($patient) {
                $query->where('patient_id', $patient->id);
            })
            ->orderBy('created_at', 'desc')
            ->get()
            ->map(function ($prescription) {
                return [
                    'id' => $prescription->id,
                    'doctor_name' => $prescription->appointment?->doctorProfile?->user?->name ?? 'Unknown',
                    'diagnosis' => $prescription->diagnosis,
                    'notes' => $prescription->notes,
                    'created_at' => $prescription->created_at,
                    'medicines' => $prescription->medicines->map(function ($medicine) {
                        return [
                            'name' => $medicine->medicine_name,
                            'dosage' => $medicine->dosage,
                            'frequency' => $medicine->frequency,
                            'duration' => $medicine->duration,
                        ];
                    }),
                ];
            });

        return response()->json($prescriptions);
    }

    public function show($id)
    {
        $patient = Auth::user();

        $prescription = Prescription::with(['appointment.doctorProfile.user', 'medicines'])
            ->whereHas('appointment', function ($query) use ($patient) {
                $query->where('patient_id', $patient->id);
            })
            ->where('id', $id)
            ->first();

        if (!$prescription) {
            return response()->json(['message' => 'Prescription not found'], 404);
        }

        return response()->json([
            'id' => $prescription->id,
            'doctor_name' => $prescription->appointment?->doctorProfile?->user?->name ?? 'Unknown',
            'diagnosis' => $prescription->diagnosis,
            'notes' => $prescription->notes,
            'created_at' => $prescription->created_at,
            'medicines' => $prescription->medicines->map(function ($medicine) {
                return [
                    'name' => $medicine->medicine_name,
                    'dosage' => $medicine->dosage,
                    'frequency' => $medicine->frequency,
                    'duration' => $medicine->duration,
                ];
            }),
        ]);
    }
}
