<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Faker\Factory as Faker;

class FoundItemsTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run()
    {
        $faker = Faker::create();

        // Assuming you already have some users in the users table.
        $userIds = DB::table('users')->pluck('id')->toArray();

        // Create 10 found items
        foreach (range(1, 10) as $index) {
            $category = ($index === 1)
                ? 'Wallet' // Matching category from LostItemsSeeder
                : $faker->word;

            $description = ($index === 1)
                ? 'A black leather wallet with a few ID cards inside.' // Matching description from LostItemsSeeder
                : $faker->sentence;

            DB::table('found_items')->insert([
                'user_id' => $faker->randomElement($userIds),
                'description' => $description,
                'category' => $category,
                'image_url' => $faker->imageUrl,
                'location_found' => $faker->address,
                'date_found' => $faker->date,
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        }
    }
}
