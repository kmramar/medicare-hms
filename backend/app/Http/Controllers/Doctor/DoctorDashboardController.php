<?php

namespace App\Http\Controllers\Doctor;

use App\Http\Controllers\Controller;
use App\Models\Appointment;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class DoctorDashboardController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        $doctor = $request->user();

        $today = now()->toDateString();

        $todayAppointments = Appointment::where('doctor_id', $doctor->id)
            ->where('date', $today)
            ->count();

        $totalPatients = Appointment::where('doctor_id', $doctor->id)
            ->distinct('patient_id')
            ->count('patient_id');

        $pendingAppointments = Appointment::where('doctor_id', $doctor->id)
            ->whereIn('status', ['pending', 'confirmed'])
            ->count();

        $completedAppointments = Appointment::where('doctor_id', $doctor->id)
            ->where('status', 'completed')
            ->count();

        $todayList = Appointment::where('doctor_id', $doctor->id)
            ->where('date', $today)
            ->with('patient:id,name')
            ->orderBy('time')
            ->get()
            ->map(fn ($apt) => [
                'id' => $apt->id,
                'patient_name' => $apt->patient->name,
                'time' => $apt->time,
                'reason' => $apt->notes ?? '',
                'status' => $apt->status,
            ]);

        return response()->json([
            'todayAppointments' => $todayAppointments,
            'totalPatients' => $totalPatients,
            'pendingAppointments' => $pendingAppointments,
            'completedAppointments' => $completedAppointments,
            'todayList' => $todayList,
        ]);
    }
}
