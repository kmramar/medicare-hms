<?php

namespace Database\Seeders;

use App\Models\PatientProfile;
use App\Models\User;
use Illuminate\Database\Seeder;

class PatientSeeder extends Seeder
{
    public function run(): void
    {
        PatientProfile::query()->delete();

        $patients = User::where('role', 'patient')->get();

        $bloodGroups = ['A+', 'A-', 'B+', 'B-', 'AB+', 'AB-', 'O+', 'O-'];
        $genders = ['male', 'female', 'other'];
        
        $addresses = [
            '123 MG Road, Mumbai',
            '45 Park Street, Kolkata',
            '78 Brigade Road, Bangalore',
            '32 Connaught Place, Delhi',
            '56 FC Road, Pune',
            '89 MG Road, Chennai',
            '11 Linking Road, Mumbai',
            '22 Hill Road, Bangalore',
            '33 Marine Drive, Mumbai',
            '44 Residency Road, Hyderabad',
        ];

        $emergencyNames = [
            'Vijay Kumar',
            'Sunita Sharma',
            'Ramesh Patel',
            'Anita Singh',
            'Mohan Reddy',
            'Lakshmi Devi',
            'Suresh Gupta',
            'Radha Krishna',
            'Bharat Singh',
            'Gita Devi',
        ];

        $index = 0;
        foreach ($patients as $patient) {
            PatientProfile::create([
                'user_id' => $patient->id,
                'date_of_birth' => now()->subYears(rand(18, 70))->format('Y-m-d'),
                'gender' => $genders[$index % count($genders)],
                'phone' => '+91' . rand(9000000000, 9999999999),
                'address' => $addresses[$index],
                'blood_type' => $bloodGroups[$index % count($bloodGroups)],
                'emergency_contact_name' => $emergencyNames[$index],
                'emergency_contact_phone' => '+91' . rand(9000000000, 9999999999),
            ]);
            $index++;
        }
    }
}
