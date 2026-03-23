<?php

namespace App\Http\Controllers\Patient;

use App\Http\Controllers\Controller;
use App\Models\Appointment;
use App\Models\Billing;
use App\Models\PatientProfile;
use App\Models\Prescription;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;

class PatientDashboardController extends Controller
{
    public function show()
    {
        $user = Auth::user();

        // Calculate stats
        $upcomingAppointments = Appointment::where('patient_id', $user->id)
            ->whereIn('status', ['confirmed', 'pending'])
            ->where('date', '>=', now()->toDateString())
            ->count();

        $completedAppointments = Appointment::where('patient_id', $user->id)
            ->where('status', 'completed')
            ->count();

        $totalPrescriptions = Prescription::where('patient_id', $user->id)->count();

        $pendingBills = Billing::where('patient_id', $user->id)
            ->where('status', '!=', 'paid')
            ->count();

        // Recent appointments
        $recentAppointments = Appointment::where('patient_id', $user->id)
            ->with('doctor:id,name', 'doctorProfile:specialty,user_id')
            ->orderBy('date', 'desc')
            ->orderBy('time', 'desc')
            ->limit(5)
            ->get()
            ->map(function ($appointment) {
                return [
                    'id' => $appointment->id,
                    'doctor_name' => $appointment->doctor->name,
                    'specialty' => $appointment->doctorProfile->specialty ?? 'General',
                    'appointment_date' => $appointment->date,
                    'appointment_time' => $appointment->time,
                    'status' => $appointment->status,
                ];
            });

        return response()->json([
            'stats' => [
                'upcomingAppointments' => $upcomingAppointments,
                'completedAppointments' => $completedAppointments,
                'totalPrescriptions' => $totalPrescriptions,
                'pendingBills' => $pendingBills,
            ],
            'recent_appointments' => $recentAppointments,
        ]);
    }

    public function update(Request $request)
    {
        /** @var User $user */ // Ye line VS Code ka 'update' error khatam kar degi
        $user = Auth::user();
        
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'date_of_birth' => 'nullable|date',
            'gender' => 'nullable|string|in:male,female,other',
            'phone' => 'nullable|string|max:20',
            'address' => 'nullable|string|max:500',
            'blood_type' => 'nullable|string|in:A+,A-,B+,B-,AB+,AB-,O+,O-',
            'emergency_contact_name' => 'nullable|string|max:255',
            'emergency_contact_phone' => 'nullable|string|max:20',
        ]);

        // User ka name update karein
        $user->update(['name' => $validated['name']]);

        // Profile update ya create karein (sirf wahi fields jo validate hui hain)
        $profile = PatientProfile::updateOrCreate(
            ['user_id' => $user->id],
            $request->only([
                'date_of_birth', 'gender', 'phone', 'address', 
                'blood_type', 'emergency_contact_name', 'emergency_contact_phone'
            ])
        );

        return response()->json([
            'message' => 'Profile updated successfully',
            'profile' => $profile
        ]);
    }

    public function uploadPhoto(Request $request)
    {
        $request->validate([
            'photo' => 'required|image|mimes:jpeg,png,jpg,gif|max:2048',
        ]);

        $user = Auth::user();
        $profile = PatientProfile::where('user_id', $user->id)->first();

        // Purani photo delete karein agar exist karti hai
        if ($profile && $profile->photo_path) {
            Storage::disk('public')->delete($profile->photo_path);
        }

        $path = $request->file('photo')->store('patient-photos', 'public');

        $profile = PatientProfile::updateOrCreate(
            ['user_id' => $user->id],
            ['photo_path' => $path]
        );

        return response()->json([
            'message' => 'Photo uploaded successfully',
            'photo_url' => Storage::url($path),
        ]);
    }
}