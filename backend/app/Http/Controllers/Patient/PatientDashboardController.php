<?php

namespace App\Http\Controllers\Patient;

use App\Http\Controllers\Controller;
use App\Models\PatientProfile;
use App\Models\User; // User model import kiya
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;

class PatientProfileController extends Controller
{
    public function show()
    {
        $user = Auth::user();
        
        // Find or create profile
        $profile = PatientProfile::firstOrCreate(['user_id' => $user->id]);

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