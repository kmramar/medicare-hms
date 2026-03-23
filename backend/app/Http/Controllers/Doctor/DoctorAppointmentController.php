<?php

namespace App\Http\Controllers\Doctor;

use App\Http\Controllers\Controller;
use App\Models\Appointment;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class DoctorAppointmentController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        $doctor = $request->user();
        $filter = $request->get('filter', 'all');

        $query = Appointment::where('doctor_id', $doctor->id)
            ->with('patient:id,name');

        $today = now()->toDateString();

        match ($filter) {
            'today' => $query->where('date', $today),
            'upcoming' => $query->where('date', '>', $today),
            'completed' => $query->where('status', 'completed'),
            'cancelled' => $query->where('status', 'cancelled'),
            default => null,
        };

        $appointments = $query->orderBy('date', 'desc')
            ->orderBy('time', 'desc')
            ->get()
            ->map(fn ($apt) => [
                'id' => $apt->id,
                'patient_name' => $apt->patient->name,
                'date' => $apt->date->toDateString(),
                'time' => $apt->time,
                'reason' => $apt->notes ?? '',
                'status' => $apt->status,
            ]);

        return response()->json(['appointments' => $appointments]);
    }

    public function update(Request $request, int $id): JsonResponse
    {
        $doctor = $request->user();
        $appointment = Appointment::where('doctor_id', $doctor->id)->findOrFail($id);

        $validated = $request->validate([
            'status' => 'required|in:confirmed,completed,cancelled',
        ]);

        $appointment->update(['status' => $validated['status']]);

        return response()->json(['message' => 'Appointment updated successfully']);
    }
}
