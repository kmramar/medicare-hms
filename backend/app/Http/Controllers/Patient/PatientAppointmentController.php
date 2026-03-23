<?php

namespace App\Http\Controllers\Patient;

use App\Http\Controllers\Controller;
use App\Models\Appointment;
use App\Models\DoctorProfile;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Schema;

class PatientAppointmentController extends Controller
{
    public function index(Request $request)
    {
        $patient = Auth::user();
        
        $query = Appointment::with('doctorProfile.user')
            ->where('patient_id', $patient->id);

        // Check if columns exist before ordering
        if (Schema::hasColumn('appointments', 'appointment_date')) {
            $query->orderBy('appointment_date', 'desc');
        }
        
        if (Schema::hasColumn('appointments', 'appointment_time')) {
            $query->orderBy('appointment_time', 'desc');
        }

        if ($request->has('status')) {
            $query->where('status', $request->status);
        }

        $appointments = $query->get()->map(function ($appointment) {
            return [
                'id' => $appointment->id,
                'doctor_name' => $appointment->doctorProfile?->user?->name ?? 'Unknown',
                'specialty' => $appointment->doctorProfile?->specialty ?? 'N/A',
                'appointment_date' => $appointment->appointment_date ?? $appointment->date ?? null,
                'appointment_time' => $appointment->appointment_time ?? $appointment->time ?? null,
                'symptoms' => $appointment->symptoms,
                'notes' => $appointment->notes,
                'status' => $appointment->status,
                'created_at' => $appointment->created_at,
            ];
        });

        return response()->json($appointments);
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'doctor_id' => 'required|exists:doctor_profiles,id',
            'appointment_date' => 'required|date|after_or_equal:today',
            'appointment_time' => 'required',
            'symptoms' => 'required|string|max:1000',
            'notes' => 'nullable|string|max:1000',
        ]);

        $doctor = DoctorProfile::findOrFail($validated['doctor_id']);

        // Check if doctor has schedule columns
        if ($doctor->available_days && $doctor->available_time_start && $doctor->available_time_end) {
            $appointmentDate = Carbon::parse($validated['appointment_date']);
            $dayOfWeek = strtolower($appointmentDate->format('l'));
            $availableDays = array_map('strtolower', explode(',', $doctor->available_days));

            if (!in_array($dayOfWeek, $availableDays)) {
                return response()->json([
                    'message' => "Doctor is not available on {$appointmentDate->format('l')}"
                ], 422);
            }

            $requestedTime = Carbon::parse($validated['appointment_time']);
            $startTime = Carbon::parse($doctor->available_time_start);
            $endTime = Carbon::parse($doctor->available_time_end);

            if ($requestedTime->lt($startTime) || $requestedTime->gt($endTime)) {
                return response()->json([
                    'message' => 'Requested time is outside doctor\'s working hours'
                ], 422);
            }
        }

        $existingAppointment = Appointment::where('doctor_id', $validated['doctor_id'])
            ->where('appointment_date', $validated['appointment_date'])
            ->where('appointment_time', $validated['appointment_time'])
            ->whereIn('status', ['pending', 'confirmed'])
            ->first();

        if ($existingAppointment) {
            return response()->json([
                'message' => 'This time slot is already booked'
            ], 422);
        }

        $appointmentData = [
            'patient_id' => Auth::id(),
            'doctor_id' => $validated['doctor_id'],
            'symptoms' => $validated['symptoms'],
            'notes' => $validated['notes'] ?? null,
            'status' => 'pending',
        ];

        // Add date and time if columns exist
        if (Schema::hasColumn('appointments', 'appointment_date')) {
            $appointmentData['appointment_date'] = $validated['appointment_date'];
        } else {
            $appointmentData['date'] = $validated['appointment_date'];
        }

        if (Schema::hasColumn('appointments', 'appointment_time')) {
            $appointmentData['appointment_time'] = $validated['appointment_time'];
        } else {
            $appointmentData['time'] = $validated['appointment_time'];
        }

        $appointment = Appointment::create($appointmentData);

        return response()->json([
            'message' => 'Appointment booked successfully',
            'appointment' => $appointment,
        ], 201);
    }

    public function cancel($id)
    {
        $patient = Auth::user();
        
        $appointment = Appointment::where('id', $id)
            ->where('patient_id', $patient->id)
            ->first();

        if (!$appointment) {
            return response()->json(['message' => 'Appointment not found'], 404);
        }

        if (!in_array($appointment->status, ['pending', 'confirmed'])) {
            return response()->json([
                'message' => 'Only pending or confirmed appointments can be cancelled'
            ], 422);
        }

        $appointment->update(['status' => 'cancelled']);

        return response()->json([
            'message' => 'Appointment cancelled successfully',
        ]);
    }
}
