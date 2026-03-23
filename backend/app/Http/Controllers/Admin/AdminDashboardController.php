<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Models\Appointment;
use App\Models\Billing;
use App\Models\Bed;
use Illuminate\Http\JsonResponse;

class AdminDashboardController extends Controller
{
    public function index(): JsonResponse
    {
        $today = now()->toDateString();

        $totalDoctors = User::where('role', 'doctor')->count();
        $totalPatients = User::where('role', 'patient')->count();
        $todayAppointments = Appointment::where('date', $today)->count();
        $totalRevenue = Billing::where('status', 'paid')->sum('amount');

        $beds = Bed::selectRaw('
            COUNT(*) as total,
            SUM(CASE WHEN status = "available" THEN 1 ELSE 0 END) as available,
            SUM(CASE WHEN status = "occupied" THEN 1 ELSE 0 END) as occupied
        ')->first();

        return response()->json([
            'totalDoctors' => $totalDoctors,
            'totalPatients' => $totalPatients,
            'todayAppointments' => $todayAppointments,
            'totalRevenue' => (float) $totalRevenue,
            'beds' => [
                'total' => (int) $beds->total,
                'available' => (int) $beds->available,
                'occupied' => (int) $beds->occupied,
            ],
        ]);
    }
}
