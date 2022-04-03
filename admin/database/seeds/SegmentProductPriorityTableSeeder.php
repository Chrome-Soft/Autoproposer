<?php

use Illuminate\Database\Seeder;

class SegmentProductPriorityTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $priorities = \App\SegmentProductPriority::all();
        if ($priorities->isEmpty()) {
            \App\SegmentProductPriority::create(['name' => 'Mindig megjelenik', 'description' => 'Ez a termék mindig meg fog jelenni az ajánlásban', 'slug' => 'always']);
            \App\SegmentProductPriority::create(['name' => 'Opcionálisan jelenik meg', 'description' => 'Ez a termék csak akkor jelenik meg, ha a rendszer nem talál jobb ajánlásokat', 'slug' => 'optional']);
        }
    }
}
