<?php

namespace Database\Seeders;

use App\Models\DoctorProfile;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Schema;

class DoctorSeeder extends Seeder
{
    public function run(): void
    {
        DoctorProfile::query()->delete();

        $doctors = User::where('role', 'doctor')->get();

        $specializations = [
            'Cardiologist',
            'Neurologist',
            'Orthopedic',
            'Pediatrician',
            'Dermatologist',
        ];

        $qualifications = [
            'MBBS, MD',
            'MBBS, MS',
            'MBBS, DNB',
            'MBBS, MD, DM',
            'MBBS, MS, MCh',
        ];

        $index = 0;
        foreach ($doctors as $doctor) {
            $data = [
                'user_id' => $doctor->id,
                'specialization' => $specializations[$index % count($specializations)],
                'fee' => rand(500, 2000),
                'status' => 'active',
            ];

            // Add optional columns if they exist and are not JSON type
            if (Schema::hasColumn('doctor_profiles', 'qualifications')) {
                $colType = Schema::getColumnType('doctor_profiles', 'qualifications');
                if ($colType !== 'json') {
                    $data['qualifications'] = $qualifications[$index % count($qualifications)];
                }
            }
            if (Schema::hasColumn('doctor_profiles', 'experience_years')) {
                $data['experience_years'] = rand(5, 20);
            }
            if (Schema::hasColumn('doctor_profiles', 'bio')) {
                $data['bio'] = 'Experienced ' . $specializations[$index % count($specializations)] . ' with over ' . rand(5, 20) . ' years of practice.';
            }

            DoctorProfile::create($data);
            $index++;
        }

        // Update with schedule info separately if columns exist
        foreach (DoctorProfile::all() as $profile) {
            $updateData = [];
            
            if (Schema::hasColumn('doctor_profiles', 'available_days')) {
                $colType = Schema::getColumnType('doctor_profiles', 'available_days');
                if ($colType === 'json') {
                    $updateData['available_days'] = json_encode(['monday', 'wednesday', 'friday']);
                } else {
                    $updateData['available_days'] = 'monday,wednesday,friday';
                }
            }
            if (Schema::hasColumn('doctor_profiles', 'available_time_start')) {
                $updateData['available_time_start'] = '09:00:00';
            }
            if (Schema::hasColumn('doctor_profiles', 'available_time_end')) {
                $updateData['available_time_end'] = '17:00:00';
            }
            if (Schema::hasColumn('doctor_profiles', 'is_active')) {
                $updateData['is_active'] = true;
            }

            if (!empty($updateData)) {
                $profile->update($updateData);
            }
        }
    }
}
