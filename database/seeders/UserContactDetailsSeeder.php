<?php

namespace Database\Seeders;

use App\Models\User\User;
use Faker\Factory as Faker;
use Illuminate\Database\Seeder;

class UserContactDetailsSeeder extends Seeder
{
    public function run(): void
    {
        $faker = Faker::create('en_NP');
        
        // Get all users without phone numbers or addresses
        $users = User::all();
        
        $this->command->info("Updating " . $users->count() . " users with phone numbers and addresses...");
        
        $updated = 0;
        foreach ($users as $user) {
            $needsUpdate = false;
            
            // Generate phone number if missing
            if (blank($user->phone_number)) {
                $user->phone_number = '98' . $faker->numerify('#########');
                $needsUpdate = true;
            }
            
            // Generate address if missing
            if (blank($user->address)) {
                $user->address = $faker->streetAddress . ", " . $faker->city;
                $needsUpdate = true;
            }
            
            if ($needsUpdate) {
                $user->save();
                $updated++;
            }
        }
        
        $this->command->info("✓ Updated {$updated} users with phone numbers and addresses!");
    }
}


