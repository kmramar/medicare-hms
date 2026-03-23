<?php

namespace App\Http\Controllers;

use App\Models\DoctorProfile;
use App\Models\PatientProfile;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\URL;

class ProfilePhotoController extends Controller
{
    public function uploadDoctorPhoto(Request $request, int $id): JsonResponse
    {
        $request->validate([
            'photo' => 'required|image|mimes:jpg,jpeg,png|max:2048',
        ]);

        $user = \App\Models\User::findOrFail($id);
        
        if ($user->role !== 'doctor') {
            return response()->json(['error' => 'User is not a doctor'], 403);
        }

        $path = $request->file('photo')->store('profile-photos/doctors', 'public');

        $profile = $user->doctorProfile;
        
        if ($profile) {
            if ($profile->photo) {
                Storage::disk('public')->delete($profile->photo);
            }
            $profile->update(['photo' => $path]);
        } else {
            DoctorProfile::create([
                'user_id' => $user->id,
                'photo' => $path,
            ]);
        }

        return response()->json([
            'message' => 'Photo uploaded successfully',
            'photo_url' => URL::to(Storage::url($path)),
        ]);
    }

    public function uploadDoctorSelfPhoto(Request $request): JsonResponse
    {
        $request->validate([
            'photo' => 'required|image|mimes:jpg,jpeg,png|max:2048',
        ]);

        $user = $request->user();
        
        if ($user->role !== 'doctor') {
            return response()->json(['error' => 'User is not a doctor'], 403);
        }

        $path = $request->file('photo')->store('profile-photos/doctors', 'public');

        $profile = $user->doctorProfile;
        
        if ($profile) {
            if ($profile->photo) {
                Storage::disk('public')->delete($profile->photo);
            }
            $profile->update(['photo' => $path]);
        } else {
            DoctorProfile::create([
                'user_id' => $user->id,
                'photo' => $path,
            ]);
        }

        return response()->json([
            'message' => 'Photo uploaded successfully',
            'photo_url' => URL::to(Storage::url($path)),
        ]);
    }

    public function uploadPatientPhoto(Request $request): JsonResponse
    {
        $request->validate([
            'photo' => 'required|image|mimes:jpg,jpeg,png|max:2048',
        ]);

        $user = $request->user();
        
        if ($user->role !== 'patient') {
            return response()->json(['error' => 'User is not a patient'], 403);
        }

        $path = $request->file('photo')->store('profile-photos/patients', 'public');

        $profile = $user->patientProfile;
        
        if ($profile) {
            if ($profile->photo_path) {
                Storage::disk('public')->delete($profile->photo_path);
            }
            $profile->update(['photo_path' => $path]);
        } else {
            PatientProfile::create([
                'user_id' => $user->id,
                'photo_path' => $path,
            ]);
        }

        return response()->json([
            'message' => 'Photo uploaded successfully',
            'photo_url' => URL::to(Storage::url($path)),
        ]);
    }
}
