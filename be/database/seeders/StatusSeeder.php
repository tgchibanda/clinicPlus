<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Status;

class StatusSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        Status::create([
            'status_level' => 0,
            'status_text' => 'Pending Payment',
            'status_description' => 'Pending Consultation Payment',
        ]);
        Status::create([
            'status_level' => 1,
            'status_text' => 'Pending Doctor Visit',
            'status_description' => 'Pending Doctor Visit',
        ]);

        Status::create([
            'status_level' => 2,
            'status_text' => 'Ready for consultation',
            'status_description' => 'Ready for consultation',
        ]);

        Status::create([
            'status_level' => 3,
            'status_text' => 'In Consultation',
            'status_description' => 'In Consultation',
        ]);
        Status::create([
            'status_level' => 4,
            'status_text' => 'Seen by doctor',
            'status_description' => 'Seen by doctor',
        ]);


    }
}
