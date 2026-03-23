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
        ];

        $statuses = ['pending', 'confirmed', 'completed', 'cancelled'];

        for ($i = 0; $i < 20; $i++) {
            $patient = $patients->random();
            $doctor = $doctors->random();
            $date = now()->addDays(rand(1, 14));
            $hour = rand(9, 17);
            $minute = rand(0, 1) * 30;

            Appointment::create([
                'patient_id' => $patient->id,
                'doctor_id' => $doctor->id,
                'date' => $date->format('Y-m-d'),
                'time' => sprintf('%02d:%02d:00', $hour, $minute),
                'notes' => $symptoms[array_rand($symptoms)],
                'status' => $statuses[array_rand($statuses)],
            ]);
        }
    }
}
