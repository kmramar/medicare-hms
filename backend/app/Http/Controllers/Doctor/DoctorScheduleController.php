<?php

namespace App\Http\Controllers\Doctor;

use App\Http\Controllers\Controller;
use App\Models\DoctorProfile;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class DoctorScheduleController extends Controller
{
    public function show(): JsonResponse
    {
        $doctor = request()->user();
        $profile = $doctor->doctorProfile;

        return response()->json([
            'schedule' => [
                'days' => $profile?->available_days ?? [],
                'time_from' => $profile?->available_time_from ?? '09:00',
                'time_to' => $profile?->available_time_to ?? '17:00',
            ],
        ]);
    }

    public function update(Request $request): JsonResponse
    {
        $doctor = $request->user();

        $validated = $request->validate([
            'days' => 'required|array|min:1',
            'days.*' => 'string',
            'time_from' => 'required|date_format:H:i',
            'time_to' => 'required|date_format:H:i',
        ]);

        $profile = $doctor->doctorProfile;

        if (!$profile) {
            $profile = DoctorProfile::create([
                'user_id' => $doctor->id,
                'specialization' => '',
                'fee' => 0,
            ]);
        }

        $profile->update([
            'available_days' => $validated['days'],
            'available_time_from' => $validated['time_from'],
            'available_time_to' => $validated['time_to'],
        ]);

        return response()->json(['message' => 'Schedule updated successfully']);
    }
}
