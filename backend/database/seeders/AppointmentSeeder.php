<?php

namespace Database\Seeders;

use App\Models\Appointment;
use App\Models\User;
use Illuminate\Database\Seeder;

class AppointmentSeeder extends Seeder
{
    public function run(): void
    {
        Appointment::query()->delete();

        $patients = User::where('role', 'patient')->get();
        $doctors = User::where('role', 'doctor')->get();

        $symptoms = [
            'Regular checkup',
            'Headache and fever',
            'Chest pain',
            'Joint pain in knee',
            'Skin rash',
            'Stomach pain',
            'Back pain',
            'Shortness of breath',
            'Eye infection',
            'Dental problem',
            'Cough and cold',
            'Allergies',
            'Diabetes checkup',
            'Blood pressure check',
            'General weakness',
        ];

        $statuses = ['pending', 'confirmed', 'completed', 'cancelled'];

        // Generate 50 appointments spread across last 6 months
        for ($i = 0; $i < 50; $i++) {
            $patient = $patients->random();
            $doctor = $doctors->random();
            
            // Random date within last 6 months
            $daysAgo = rand(1, 180);
            $date = now()->subDays($daysAgo);
            $hour = rand(9, 17);
            $minute = rand(0, 1) * 30;

            // More likely to be completed/cancelled for past dates
            if ($daysAgo > 7) {
                $status = $statuses[rand(2, 3)]; // completed or cancelled
            } else {
                $status = $statuses[rand(0, 2)]; // any status
            }

            Appointment::create([
                'patient_id' => $patient->id,
                'doctor_id' => $doctor->id,
                'date' => $date->format('Y-m-d'),
                'time' => sprintf('%02d:%02d:00', $hour, $minute),
                'notes' => $symptoms[array_rand($symptoms)],
                'status' => $status,
            ]);
        }
    }
}
