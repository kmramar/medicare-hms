<?php

namespace App\Http\Controllers\Patient;

use App\Http\Controllers\Controller;
use App\Models\DoctorProfile;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Schema;

class PatientDoctorController extends Controller
{
    public function index(Request $request)
    {
        $query = DoctorProfile::with('user');

        if ($request->has('specialty') && $request->specialty) {
            $query->where('specialty', 'like', '%' . $request->specialty . '%');
        }

        if ($request->has('search') && $request->search) {
            $search = $request->search;
            $query->whereHas('user', function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%");
            });
        }

        // Check if is_active column exists
        if (Schema::hasColumn('doctor_profiles', 'is_active')) {
            $query->where('is_active', true);
        }

        // Check if experience_years column exists for ordering
        if (Schema::hasColumn('doctor_profiles', 'experience_years')) {
            $query->orderBy('experience_years', 'desc');
        }

        $doctors = $query->get();

        return response()->json($doctors);
    }

    public function show($id)
    {
        $doctor = DoctorProfile::with('user')->where('id', $id)->first();

        if (!$doctor) {
            return response()->json(['message' => 'Doctor not found'], 404);
        }

        return response()->json($doctor);
    }
}
