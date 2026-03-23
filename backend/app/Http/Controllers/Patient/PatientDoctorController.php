<?php

namespace App\Http\Controllers\Patient;

use App\Http\Controllers\Controller;
use App\Models\DoctorProfile;
use Illuminate\Http\Request;

class PatientDoctorController extends Controller
{
    public function index(Request $request)
    {
        $query = DoctorProfile::with('user')->where('is_active', true);

        if ($request->has('specialty') && $request->specialty) {
            $query->where('specialty', 'like', '%' . $request->specialty . '%');
        }

        if ($request->has('search') && $request->search) {
            $search = $request->search;
            $query->whereHas('user', function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%");
            });
        }

        $doctors = $query->orderBy('experience_years', 'desc')->get();

        return response()->json($doctors);
    }

    public function show($id)
    {
        $doctor = DoctorProfile::with('user')->where('id', $id)->where('is_active', true)->first();

        if (!$doctor) {
            return response()->json(['message' => 'Doctor not found'], 404);
        }

        return response()->json($doctor);
    }
}
