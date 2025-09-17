<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Options;

class OptionSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        Options::create([
            'category' => 'payment',
            'type' => 'decimal',
            'name' => 'consultation_fee',
            'value' => '45.00',
            'description' => 'Consultation fee',
        ]);
    }
}
