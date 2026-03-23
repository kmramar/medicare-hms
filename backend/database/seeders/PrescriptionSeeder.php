<?php

namespace Database\Seeders;

use App\Models\Appointment;
use App\Models\Prescription;
use Illuminate\Database\Seeder;

class PrescriptionSeeder extends Seeder
{
    public function run(): void
    {
        Prescription::query()->delete();

        $completedAppointments = Appointment::where('status', 'completed')->get();

        $medicinesList = [
            ['name' => 'Paracetamol', 'dosage' => '500mg', 'duration' => '3 days'],
            ['name' => 'Amoxicillin', 'dosage' => '250mg', 'duration' => '5 days'],
            ['name' => 'Ibuprofen', 'dosage' => '400mg', 'duration' => '3 days'],
            ['name' => 'Aspirin', 'dosage' => '325mg', 'duration' => '5 days'],
            ['name' => 'Cetirizine', 'dosage' => '10mg', 'duration' => '3 days'],
            ['name' => 'Metformin', 'dosage' => '500mg', 'duration' => '30 days'],
            ['name' => 'Omeprazole', 'dosage' => '20mg', 'duration' => '14 days'],
            ['name' => 'Azithromycin', 'dosage' => '250mg', 'duration' => '5 days'],
            ['name' => 'Metoprolol', 'dosage' => '25mg', 'duration' => '30 days'],
            ['name' => 'Atorvastatin', 'dosage' => '10mg', 'duration' => '90 days'],
            ['name' => 'Amlodipine', 'dosage' => '5mg', 'duration' => '30 days'],
            ['name' => 'Levocetirizine', 'dosage' => '5mg', 'duration' => '7 days'],
            ['name' => 'Pantoprazole', 'dosage' => '40mg', 'duration' => '14 days'],
            ['name' => 'Montelukast', 'dosage' => '10mg', 'duration' => '30 days'],
            ['name' => 'Diclofenac', 'dosage' => '50mg', 'duration' => '5 days'],
        ];

        $diagnoses = [
            'Upper respiratory tract infection',
            'Acute gastroenteritis',
            'Hypertension follow-up',
            'Type 2 diabetes management',
            'Allergic rhinitis',
            'Acute back pain',
            'Tension headache',
            'Skin infection',
            'Conjunctivitis',
            'General health checkup',
            'Chest infection',
            'Joint pain evaluation',
        ];

        foreach ($completedAppointments as $appointment) {
            $numMedicines = rand(2, 4);
            $shuffled = $medicinesList;
            shuffle($shuffled);
            $selectedMedicines = array_slice($shuffled, 0, $numMedicines);

            Prescription::create([
                'appointment_id' => $appointment->id,
                'patient_id' => $appointment->patient_id,
                'doctor_id' => $appointment->doctor_id,
                'diagnosis' => $diagnoses[array_rand($diagnoses)],
                'medicines' => json_encode($selectedMedicines),
                'instructions' => 'Follow up after ' . rand(7, 14) . ' days if symptoms persist. Take medicines after food.',
            ]);
        }
    }
}
