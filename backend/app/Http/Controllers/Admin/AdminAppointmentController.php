<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Appointment;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class AdminAppointmentController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        $query = Appointment::with(['patient:id,name', 'doctor:id,name']);

        if ($request->filled('date')) {
            $query->where('date', $request->date);
        }

        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        if ($request->filled('doctor_id')) {
            $query->where('doctor_id', $request->doctor_id);
        }

        $appointments = $query->latest()->get()->map(fn ($apt) => [
            'id' => $apt->id,
            'patient_name' => $apt->patient->name,
            'doctor_name' => $apt->doctor->name,
            'date' => $apt->date->toDateString(),
            'time' => $apt->time,
            'status' => $apt->status,
            'notes' => $apt->notes,
        ]);

        return response()->json(['appointments' => $appointments]);
    }

    public function update(Request $request, int $id): JsonResponse
    {
        $appointment = Appointment::findOrFail($id);

        $validated = $request->validate([
            'status' => 'required|in:pending,confirmed,completed,cancelled',
        ]);

        $appointment->update(['status' => $validated['status']]);

        return response()->json(['message' => 'Appointment updated successfully']);
    }
}
