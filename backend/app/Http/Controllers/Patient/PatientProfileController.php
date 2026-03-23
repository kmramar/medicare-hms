<?php

namespace App\Http\Controllers\Patient;

use App\Http\Controllers\Controller;
use App\Models\PatientProfile;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;

class PatientProfileController extends Controller
{
    public function show()
    {
        $user = Auth::user();
        
        $profile = PatientProfile::where('user_id', $user->id)->first();

        if (!$profile) {
            $profile = PatientProfile::create([
                'user_id' => $user->id,
            ]);
        }

        return response()->json([
            'id' => $profile->id,
            'user_id' => $profile->user_id,
            'date_of_birth' => $profile->date_of_birth,
            'gender' => $profile->gender,
            'phone' => $profile->phone,
            'address' => $profile->address,
            'blood_type' => $profile->blood_type,
            'emergency_contact_name' => $profile->emergency_contact_name,
            'emergency_contact_phone' => $profile->emergency_contact_phone,
            'photo_url' => $profile->photo_path 
                ? Storage::url($profile->photo_path) 
                : null,
        ]);
    }

    public function update(Request $request)
    {
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

        $user->update(['name' => $validated['name']]);

        $profile = PatientProfile::updateOrCreate(
            ['user_id' => $user->id],
            [
                'date_of_birth' => $validated['date_of_birth'] ?? null,
                'gender' => $validated['gender'] ?? null,
                'phone' => $validated['phone'] ?? null,
                'address' => $validated['address'] ?? null,
                'blood_type' => $validated['blood_type'] ?? null,
                'emergency_contact_name' => $validated['emergency_contact_name'] ?? null,
                'emergency_contact_phone' => $validated['emergency_contact_phone'] ?? null,
            ]
        );

        return response()->json([
            'message' => 'Profile updated successfully',
            'profile' => [
                'id' => $profile->id,
                'user_id' => $profile->user_id,
                'date_of_birth' => $profile->date_of_birth,
                'gender' => $profile->gender,
                'phone' => $profile->phone,
                'address' => $profile->address,
                'blood_type' => $profile->blood_type,
                'emergency_contact_name' => $profile->emergency_contact_name,
                'emergency_contact_phone' => $profile->emergency_contact_phone,
            ],
        ]);
    }

    public function uploadPhoto(Request $request)
    {
        $request->validate([
            'photo' => 'required|image|mimes:jpeg,png,jpg,gif|max:2048',
        ]);

        $user = Auth::user();
        $profile = PatientProfile::where('user_id', $user->id)->first();

        if ($profile && $profile->photo_path) {
            Storage::delete($profile->photo_path);
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
