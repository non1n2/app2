<?php

namespace Database\Seeders;

// use Illuminate\Database\Console\Seeds\WithoutModelEvents;

use App\Models\Part;
use App\Models\User;
use App\Models\Table;
use App\Models\Category;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        $this->call([
            UserSeeder::class,
            GormentSeeder::class
        ]);
        foreach (Part::all() as $part) {
            $part->tables()->sync(
                Table::all()->random(rand(1, 7))->pluck('id')->toArray()
            );
        }
    }
}
