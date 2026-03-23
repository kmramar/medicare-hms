<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class UserSeeder extends Seeder
{
    public function run(): void
    {
        // Delete existing users to avoid duplicates
        User::where('email', 'like', '%@medicare.com')
            ->orWhere('email', 'like', '%@email.com')
            ->delete();

        // Admin User
        User::create([
            'name' => 'Admin User',
            'email' => 'admin@medicare.com',
            'password' => Hash::make('password'),
            'role' => 'admin',
        ]);

        // 5 Doctors with Indian names
        $doctorNames = [
            'Dr. Rajesh Sharma',
            'Dr. Priya Patel',
            'Dr. Amit Singh',
            'Dr. Sneha Kumar',
            'Dr. Vikram Reddy',
        ];

        foreach ($doctorNames as $name) {
            $nameWithoutPrefix = preg_replace('/^Dr\.\s*/', '', $name);
            User::create([
                'name' => $name,
                'email' => strtolower(str_replace(' ', '.', $nameWithoutPrefix)) . '@medicare.com',
                'password' => Hash::make('password'),
                'role' => 'doctor',
            ]);
        }

        // 10 Patients with Indian names
        $patientNames = [
            'Rahul Verma',
            'Anjali Gupta',
            'Suresh Kumar',
            'Meera Shah',
            'Raj Malhotra',
            'Sunita Devi',
            'Ajay Singh',
            'Pooja Sharma',
            'Deepak Patel',
            'Kavita Reddy',
        ];

        foreach ($patientNames as $name) {
            User::create([
                'name' => $name,
                'email' => strtolower(str_replace(' ', '.', $name)) . '@email.com',
                'password' => Hash::make('password'),
                'role' => 'patient',
            ]);
        }
    }
}
