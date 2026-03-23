<?php

namespace Database\Seeders;

use App\Models\Billing;
use App\Models\Appointment;
use Illuminate\Database\Seeder;

class BillingSeeder extends Seeder
{
    public function run(): void
    {
        Billing::query()->delete();

        // Get completed appointments and create billing for each
        $completedAppointments = Appointment::where('status', 'completed')->get();
        
        $invoiceCounter = 1000;

        foreach ($completedAppointments as $appointment) {
            $amount = rand(500, 5000);
            $date = \Carbon\Carbon::parse($appointment->date);
            $status = rand(0, 2) === 0 ? 'paid' : (rand(0, 1) === 0 ? 'unpaid' : 'partial');

            Billing::create([
                'patient_id' => $appointment->patient_id,
                'appointment_id' => $appointment->id,
                'invoice_number' => 'INV-' . $invoiceCounter++,
                'amount' => $amount,
                'status' => $status,
                'date' => $date->format('Y-m-d'),
                'due_date' => $date->addDays(30)->format('Y-m-d'),
            ]);
        }

        // Add some additional billing records for better chart data (spread across 6 months)
        $patients = \App\Models\User::where('role', 'patient')->get();
        
        for ($i = 0; $i < 20; $i++) {
            $patient = $patients->random();
            $daysAgo = rand(1, 180);
            $date = now()->subDays($daysAgo);
            $amount = rand(500, 5000);
            $status = ['paid', 'unpaid', 'partial'][rand(0, 2)];

            Billing::create([
                'patient_id' => $patient->id,
                'invoice_number' => 'INV-' . $invoiceCounter++,
                'amount' => $amount,
                'status' => $status,
                'date' => $date->format('Y-m-d'),
                'due_date' => $date->addDays(30)->format('Y-m-d'),
            ]);
        }
    }
}
