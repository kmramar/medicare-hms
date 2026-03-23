<?php

namespace Database\Seeders;

use App\Models\Billing;
use App\Models\User;
use Illuminate\Database\Seeder;

class BillingSeeder extends Seeder
{
    public function run(): void
    {
        Billing::query()->delete();

        $patients = User::where('role', 'patient')->get();
        
        $invoiceCounter = 1000;

        for ($i = 0; $i < 15; $i++) {
            $patient = $patients->random();
            $amount = rand(500, 5000);
            $date = now()->subDays(rand(1, 30));
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
