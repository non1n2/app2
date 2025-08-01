<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class UserSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        User::factory($state = [
            'name' => 'ahmad',
            'email' => 'ahmadghafeer@gmail.com', //password is "password"
        ])->asAdmin()->create();
        User::factory(10)->asCustomer()->create();
    }
}
