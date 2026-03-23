<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Billing;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class AdminBillingController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        $query = Billing::with('patient:id,name', 'appointment');

        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        $billings = $query->latest()->get()->map(fn ($billing) => [
            'id' => $billing->id,
            'patient_name' => $billing->patient->name,
            'invoice_number' => $billing->invoice_number,
            'amount' => (float) $billing->amount,
            'status' => $billing->status,
            'date' => $billing->date->toDateString(),
            'due_date' => $billing->due_date->toDateString(),
        ]);

        return response()->json(['billings' => $billings]);
    }
}
