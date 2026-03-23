<?php

namespace App\Http\Controllers\Patient;

use App\Http\Controllers\Controller;
use App\Models\Billing;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class PatientBillingController extends Controller
{
    public function index()
    {
        $patient = Auth::user();

        $billings = Billing::with(['appointment.doctorProfile.user', 'items'])
            ->whereHas('appointment', function ($query) use ($patient) {
                $query->where('patient_id', $patient->id);
            })
            ->orderBy('created_at', 'desc')
            ->get()
            ->map(function ($billing) {
                return [
                    'id' => $billing->id,
                    'appointment_id' => $billing->appointment_id,
                    'doctor_name' => $billing->appointment?->doctorProfile?->user?->name ?? 'Unknown',
                    'amount' => $billing->total_amount,
                    'status' => $billing->status,
                    'due_date' => $billing->due_date,
                    'paid_at' => $billing->paid_at,
                    'created_at' => $billing->created_at,
                    'items' => $billing->items->map(function ($item) {
                        return [
                            'description' => $item->description,
                            'amount' => $item->amount,
                        ];
                    }),
                ];
            });

        return response()->json($billings);
    }

    public function show($id)
    {
        $patient = Auth::user();

        $billing = Billing::with(['appointment.doctorProfile.user', 'items'])
            ->whereHas('appointment', function ($query) use ($patient) {
                $query->where('patient_id', $patient->id);
            })
            ->where('id', $id)
            ->first();

        if (!$billing) {
            return response()->json(['message' => 'Bill not found'], 404);
        }

        return response()->json([
            'id' => $billing->id,
            'appointment_id' => $billing->appointment_id,
            'doctor_name' => $billing->appointment?->doctorProfile?->user?->name ?? 'Unknown',
            'amount' => $billing->total_amount,
            'status' => $billing->status,
            'due_date' => $billing->due_date,
            'paid_at' => $billing->paid_at,
            'created_at' => $billing->created_at,
            'items' => $billing->items->map(function ($item) {
                return [
                    'description' => $item->description,
                    'amount' => $item->amount,
                ];
            }),
        ]);
    }

    public function pay($id)
    {
        $patient = Auth::user();

        $billing = Billing::whereHas('appointment', function ($query) use ($patient) {
                $query->where('patient_id', $patient->id);
            })
            ->where('id', $id)
            ->first();

        if (!$billing) {
            return response()->json(['message' => 'Bill not found'], 404);
        }

        if ($billing->status === 'paid') {
            return response()->json(['message' => 'Bill is already paid'], 422);
        }

        $billing->update([
            'status' => 'paid',
            'paid_at' => now(),
        ]);

        return response()->json([
            'message' => 'Payment successful',
        ]);
    }
}
