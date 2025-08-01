<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class GormentSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {   
        $count = 0;

        // Create a Garment instance
        $garment = \App\Models\Garment::factory()->create();

        for ($j = 0; $j < 7; $j++) {
            // Create a Table instance and associate it with the garment
            $table = \App\Models\Table::factory()->create(['garment_id' => $garment->id]);

            // Create a random number of Parts associated with the Table
            $numParts = rand(1, 4);
            for ($i = 0; $i < $numParts; $i++) {
                \App\Models\Part::factory()->create([
                    'assembly_id' => ++$count
                ]);
            }
        }

        // Create 10 Products associated with the Garment
        \App\Models\Product::factory()->count(10)->create(['garment_id' => $garment->id]);
    }
}
