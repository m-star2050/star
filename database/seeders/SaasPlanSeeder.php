<?php

namespace Database\Seeders;

use App\Models\SaasPlan;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;

class SaasPlanSeeder extends Seeder
{
    public function run(): void
    {
        $plans = [
            [
                'name' => 'Free',
                'slug' => 'free',
                'code' => 'PLAN_FREE',
                'monthly_price' => 0,
                'annual_price' => 0,
                'property_limit' => 10,
                'featured_limit' => 2,
                'staff_limit' => 5,
                'duration_days' => 30,
                'features' => [
                    'properties' => 10,
                    'storage' => '2GB',
                    'support' => 'Email',
                ],
            ],
            [
                'name' => 'Standard',
                'slug' => 'standard',
                'code' => 'PLAN_STANDARD',
                'monthly_price' => 399,
                'annual_price' => 3990,
                'property_limit' => 250,
                'featured_limit' => 25,
                'staff_limit' => 50,
                'duration_days' => 30,
                'features' => [
                    'properties' => 250,
                    'storage' => '50GB',
                    'support' => 'Chat',
                ],
            ],
            [
                'name' => 'Premium',
                'slug' => 'premium',
                'code' => 'PLAN_PREMIUM',
                'monthly_price' => 799,
                'annual_price' => 7990,
                'property_limit' => null,
                'featured_limit' => null,
                'staff_limit' => null,
                'duration_days' => 30,
                'features' => [
                    'properties' => 'unlimited',
                    'storage' => '200GB',
                    'support' => 'Dedicated',
                ],
            ],
        ];

        foreach ($plans as $plan) {
            SaasPlan::updateOrCreate(
                ['slug' => $plan['slug']],
                array_merge($plan, ['code' => $plan['code'] ?: Str::upper(Str::random(8))])
            );
        }
    }
}

