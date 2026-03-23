<?php

namespace App\Http\Controllers\Doctor;

use App\Http\Controllers\Controller;
use App\Models\Appointment;
use App\Models\User;
use Illuminate\Http\JsonResponse;

class DoctorPatientController extends Controller
{
    public function index(): JsonResponse
    {
        $doctor = request()->user();

        $patientIds = Appointment::where('doctor_id', $doctor->id)
            ->distinct()
            ->pluck('patient_id');

        $patients = User::whereIn('id', $patientIds)
            ->get()
            ->map(fn ($patient) => [
                'id' => $patient->id,
                'name' => $patient->name,
                'email' => $patient->email,
                'phone' => $patient->phone ?? '',
                'gender' => $patient->gender ?? 'N/A',
                'date_of_birth' => $patient->date_of_birth ?? 'N/A',
                'blood_group' => $patient->blood_group ?? 'N/A',
            ]);

        return response()->json(['patients' => $patients]);
    }

    public function show(int $id): JsonResponse
    {
        $doctor = request()->user();

        $patient = User::where('id', $id)->firstOrFail();

        $appointments = Appointment::where('doctor_id', $doctor->id)
            ->where('patient_id', $id)
            ->orderBy('date', 'desc')
            ->get()
            ->map(fn ($apt) => [
                'id' => $apt->id,
                'date' => $apt->date->toDateString(),
                'time' => $apt->time,
                'reason' => $apt->notes ?? '',
                'status' => $apt->status,
            ]);

        return response()->json([
            'patient' => [
                'id' => $patient->id,
                'name' => $patient->name,
                'email' => $patient->email,
                'phone' => $patient->phone ?? '',
                'gender' => $patient->gender ?? 'N/A',
                'date_of_birth' => $patient->date_of_birth ?? 'N/A',
                'blood_group' => $patient->blood_group ?? 'N/A',
            ],
            'appointments' => $appointments,
        ]);
    }
}
