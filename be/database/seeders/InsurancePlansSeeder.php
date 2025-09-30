<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class InsurancePlansSeeder extends Seeder
{
    /**
     * Run the database seeds.
     * 
     * This seeder creates 5 insurance plans with adult and child pricing.
     * Adult price applies to ages 18 and above.
     * Child price applies to ages below 18.
     */
    public function run(): void
    {
        $plans = [
            [
                'slug' => 'economy',
                'name' => 'Economy',
                'description' => 'Basic health insurance coverage for individuals and families. Includes essential medical services and preventive care.',
                'price_adult' => 7.00,
                'price_child' => 5.00,
                'active' => true,
            ],
            [
                'slug' => 'economy-plus',
                'name' => 'Economy Plus',
                'description' => 'Enhanced basic coverage with additional benefits including dental check-ups and vision care.',
                'price_adult' => 18.00,
                'price_child' => 15.00,
                'active' => true,
            ],
            [
                'slug' => 'medium',
                'name' => 'Medium',
                'description' => 'Comprehensive coverage for medium healthcare needs. Includes specialist consultations and diagnostic tests.',
                'price_adult' => 48.00,
                'price_child' => 45.00,
                'active' => true,
            ],
            [
                'slug' => 'executive',
                'name' => 'Executive',
                'description' => 'Premium coverage with extensive healthcare benefits including hospitalization and emergency services.',
                'price_adult' => 85.00,
                'price_child' => 80.00,
                'active' => true,
            ],
            [
                'slug' => 'executive-plus',
                'name' => 'Executive Plus',
                'description' => 'Top-tier comprehensive healthcare coverage with premium services, international coverage, and priority care.',
                'price_adult' => 200.00,
                'price_child' => 150.00,
                'active' => true,
            ],
        ];

        // Use transaction for data integrity
        DB::beginTransaction();

        try {
            // Clear existing plans if any (optional - remove if you want to keep existing data)
            // DB::table('insurance_plans')->truncate();

            $timestamp = Carbon::now();

            foreach ($plans as $plan) {
                // Check if plan already exists by slug
                $exists = DB::table('insurance_plans')
                    ->where('slug', $plan['slug'])
                    ->exists();

                if (!$exists) {
                    DB::table('insurance_plans')->insert([
                        'slug' => $plan['slug'],
                        'name' => $plan['name'],
                        'description' => $plan['description'],
                        'price_adult' => $plan['price_adult'],
                        'price_child' => $plan['price_child'],
                        'active' => $plan['active'],
                        'created_at' => $timestamp,
                        'updated_at' => $timestamp,
                    ]);

                    $this->command->info("✓ Created plan: {$plan['name']}");
                } else {
                    $this->command->warn("⚠ Plan already exists: {$plan['name']}");
                }
            }

            DB::commit();

            $this->command->info('');
            $this->command->info('Insurance plans seeding completed successfully!');
            $this->command->info('Total plans available: ' . DB::table('insurance_plans')->count());
            
        } catch (\Exception $e) {
            DB::rollBack();
            $this->command->error('Error seeding insurance plans: ' . $e->getMessage());
            throw $e;
        }
    }
}