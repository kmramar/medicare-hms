<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Models\Appointment;
use Illuminate\Http\JsonResponse;

class AdminPatientController extends Controller
{
    public function index(): JsonResponse
    {
        $patients = User::where('role', 'patient')
            ->latest()
            ->get()
            ->map(function ($user) {
                return [
                    'id' => $user->id,
                    'name' => $user->name,
                    'email' => $user->email,
                    'phone' => $user->phone ?? '',
                    'gender' => $user->gender ?? 'N/A',
                    'date_of_birth' => $user->date_of_birth ?? 'N/A',
                    'blood_group' => $user->blood_group ?? 'N/A',
                    'address' => $user->address ?? '',
                ];
            });

        return response()->json(['patients' => $patients]);
    }

    public function show(int $id): JsonResponse
    {
        $patient = User::where('role', 'patient')->findOrFail($id);

        $appointments = Appointment::where('patient_id', $id)
            ->with('doctor:id,name')
            ->latest()
            ->get()
            ->map(fn ($apt) => [
                'id' => $apt->id,
                'doctor_name' => $apt->doctor->name,
                'date' => $apt->date->toDateString(),
                'time' => $apt->time,
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
                'address' => $patient->address ?? '',
            ],
            'appointments' => $appointments,
        ]);
    }
}
