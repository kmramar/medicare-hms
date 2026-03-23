<?php

namespace App\Http\Controllers\Doctor;

use App\Http\Controllers\Controller;
use App\Models\DoctorProfile;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class DoctorProfileController extends Controller
{
    public function show(): JsonResponse
    {
        $user = request()->user();
        $profile = $user->doctorProfile;

        return response()->json([
            'profile' => [
                'name' => $user->name,
                'email' => $user->email,
                'phone' => $user->phone ?? '',
                'specialization' => $profile?->specialization ?? '',
                'fee' => (float) ($profile?->fee ?? 0),
                'photo' => $profile?->photo,
            ],
        ]);
    }

    public function update(Request $request): JsonResponse
    {
        $user = $request->user();

        $validated = $request->validate([
            'name' => 'sometimes|string|max:255',
            'phone' => 'sometimes|string|max:20',
            'specialization' => 'sometimes|string|max:255',
            'fee' => 'sometimes|numeric|min:0',
        ]);

        $user->update([
            'name' => $validated['name'] ?? $user->name,
            'phone' => $validated['phone'] ?? $user->phone,
        ]);

        $profile = $user->doctorProfile;

        if ($profile) {
            $profile->update([
                'specialization' => $validated['specialization'] ?? $profile->specialization,
                'fee' => $validated['fee'] ?? $profile->fee,
            ]);
        } else {
            DoctorProfile::create([
                'user_id' => $user->id,
                'specialization' => $validated['specialization'] ?? '',
                'fee' => $validated['fee'] ?? 0,
            ]);
        }

        return response()->json(['message' => 'Profile updated successfully']);
    }

    public function uploadPhoto(Request $request): JsonResponse
    {
        $user = $request->user();

        $request->validate([
            'photo' => 'required|image|max:2048',
        ]);

        $path = $request->file('photo')->store('doctors', 'public');

        $profile = $user->doctorProfile;

        if ($profile) {
            if ($profile->photo) {
                Storage::disk('public')->delete($profile->photo);
            }
            $profile->update(['photo' => $path]);
        } else {
            DoctorProfile::create([
                'user_id' => $user->id,
                'specialization' => '',
                'fee' => 0,
                'photo' => $path,
            ]);
        }

        return response()->json(['message' => 'Photo uploaded successfully', 'photo' => $path]);
    }
}
