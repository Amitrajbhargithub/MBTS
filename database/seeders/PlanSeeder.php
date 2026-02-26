<?php

namespace Database\Seeders;

use App\Models\Plan;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class PlanSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $plans = [
            [
                'name' => 'Basic Plan',
                'description' => '',
                'slug' => 'home-basic-plan',
                'speed' => '15 MBPS',
                'amount' => 999,
                'type' => 'home'
            ],
            [
                'name' => 'Standard Plan',
                'description' => '',
                'slug' => 'home-standard-plan',
                'speed' => '20 MBPS',
                'amount' => 1599,
                'type' => 'home'
            ],
            [
                'name' => 'Premium Plan',
                'description' => '',
                'slug' => 'home-premium-plan',
                'speed' => '25 MBPS',
                'amount' => 2500,
                'type' => 'home'
            ],
            [
                'name' => 'Basic Plan',
                'description' => '',
                'slug' => 'business-basic-plan',
                'speed' => '10 MBPS',
                'amount' => 3500,
                'type' => 'business'
            ],
            [
                'name' => 'Standard Plan',
                'description' => '',
                'slug' => 'business-standard-plan',
                'speed' => '20 MBPS',
                'amount' => 5200,
                'type' => 'business'
            ],
            [
                'name' => 'Premium Plan',
                'description' => '',
                'slug' => 'business-premium-plan',
                'speed' => '30 MBPS',
                'amount' => 7500,
                'type' => 'business'
            ],
        ];

        foreach ($plans as $plan) {
            Plan::create($plan);
        }
    }
}
