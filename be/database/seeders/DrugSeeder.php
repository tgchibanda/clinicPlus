<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;

class DrugSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $drugs = [
            [
                'name' => 'Paracetamol 500mg',
                'description' => 'Pain reliever and fever reducer',
                'category' => 'Analgesic',
                'selling_price' => 5.00,
                'stock_quantity' => 100,
                'minimum_stock_level' => 20,
                'unit' => 'tablets'
            ],
            [
                'name' => 'Amoxicillin 250mg',
                'description' => 'Antibiotic for bacterial infections',
                'category' => 'Antibiotic',
                'selling_price' => 12.50,
                'stock_quantity' => 75,
                'minimum_stock_level' => 15,
                'unit' => 'capsules'
            ],
            [
                'name' => 'Ibuprofen 400mg',
                'description' => 'Anti-inflammatory pain reliever',
                'category' => 'NSAID',
                'selling_price' => 8.00,
                'stock_quantity' => 80,
                'minimum_stock_level' => 20,
                'unit' => 'tablets'
            ],
            [
                'name' => 'Cough Syrup',
                'description' => 'For dry and productive cough',
                'category' => 'Respiratory',
                'selling_price' => 15.00,
                'stock_quantity' => 30,
                'minimum_stock_level' => 10,
                'unit' => 'bottles'
            ],
            [
                'name' => 'Multivitamin',
                'description' => 'Daily vitamin supplement',
                'category' => 'Supplement',
                'selling_price' => 25.00,
                'stock_quantity' => 50,
                'minimum_stock_level' => 10,
                'unit' => 'bottles'
            ]
        ];

        foreach ($drugs as $drug) {
            Drug::create($drug);
        }
    }
}