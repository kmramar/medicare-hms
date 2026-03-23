<?php

namespace Database\Seeders;

use App\Models\Bed;
use Illuminate\Database\Seeder;

class BedSeeder extends Seeder
{
    public function run(): void
    {
        Bed::query()->delete();

        $wards = ['General', 'ICU', 'Pediatric', 'Maternity', 'Emergency'];
        $statuses = ['available', 'occupied', 'under_maintenance'];
        
        $bedNumber = 1;

        for ($floor = 1; $floor <= 3; $floor++) {
            foreach ($wards as $ward) {
                for ($i = 0; $i < 5; $i++) {
                    Bed::create([
                        'bed_number' => 'BED-' . str_pad($bedNumber++, 3, '0', STR_PAD_LEFT),
                        'ward' => $ward,
                        'floor' => $floor,
                        'status' => $statuses[rand(0, 2)],
                    ]);
                }
            }
        }
    }
}
