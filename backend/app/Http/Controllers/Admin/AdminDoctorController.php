<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Models\DoctorProfile;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rule;

class AdminDoctorController extends Controller
{
    public function index(): JsonResponse
    {
        $doctors = User::where('role', 'doctor')
            ->with('doctorProfile')
            ->latest()
            ->get()
            ->map(function ($user) {
                return [
                    'id' => $user->id,
                    'name' => $user->name,
                    'email' => $user->email,
                    'phone' => $user->phone ?? '',
                    'specialization' => $user->doctorProfile?->specialization ?? '',
                    'fee' => (float) ($user->doctorProfile?->fee ?? 0),
                    'photo' => $user->doctorProfile?->photo,
                    'status' => $user->doctorProfile?->status ?? 'inactive',
                ];
            });

        return response()->json(['doctors' => $doctors]);
    }

    public function store(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|string|email|max:255|unique:users',
            'phone' => 'required|string|max:20',
            'specialization' => 'required|string|max:255',
            'fee' => 'required|numeric|min:0',
            'status' => 'required|in:active,inactive',
        ]);

        $user = User::create([
            'name' => $validated['name'],
            'email' => $validated['email'],
            'password' => Hash::make('doctor123'),
            'role' => 'doctor',
        ]);

        $user->doctorProfile()->create([
            'specialization' => $validated['specialization'],
            'fee' => $validated['fee'],
            'status' => $validated['status'],
        ]);

        return response()->json(['message' => 'Doctor created successfully', 'doctor' => $user], 201);
    }

    public function update(Request $request, int $id): JsonResponse
    {
        $user = User::where('role', 'doctor')->with('doctorProfile')->findOrFail($id);

        $validated = $request->validate([
            'name' => 'sometimes|string|max:255',
            'email' => ['sometimes', 'string', 'email', 'max:255', Rule::unique('users')->ignore($user->id)],
            'phone' => 'sometimes|string|max:20',
            'specialization' => 'sometimes|string|max:255',
            'fee' => 'sometimes|numeric|min:0',
            'status' => 'sometimes|in:active,inactive',
        ]);

        $user->update([
            'name' => $validated['name'] ?? $user->name,
            'email' => $validated['email'] ?? $user->email,
        ]);

        if ($user->doctorProfile) {
            $user->doctorProfile->update([
                'specialization' => $validated['specialization'] ?? $user->doctorProfile->specialization,
                'fee' => $validated['fee'] ?? $user->doctorProfile->fee,
                'status' => $validated['status'] ?? $user->doctorProfile->status,
            ]);
        }

        return response()->json(['message' => 'Doctor updated successfully']);
    }

    public function destroy(int $id): JsonResponse
    {
        $user = User::where('role', 'doctor')->findOrFail($id);

        if ($user->doctorProfile) {
            $user->doctorProfile->delete();
        }

        $user->delete();

        return response()->json(['message' => 'Doctor deleted successfully']);
    }
}
