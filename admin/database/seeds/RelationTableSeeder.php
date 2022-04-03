<?php

use Illuminate\Database\Seeder;

class RelationTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $relations = \App\Relation::all();
        if ($relations->isEmpty()) {
            \App\Relation::create(['name' => 'Egyenlő', 'symbol' => '=']);
            \App\Relation::create(['name' => 'Nem egyenlő', 'symbol' => '!=']);
            \App\Relation::create(['name' => 'Kisebb, vagy egyenlő', 'symbol' => '<=']);
            \App\Relation::create(['name' => 'Nagyobb, vagy egyenlő', 'symbol' => '>=']);
            \App\Relation::create(['name' => 'Tartalmaz egy szöveget', 'symbol' => 'LIKE']);
            \App\Relation::create(['name' => 'Nem tartalmaz egy szöveget', 'symbol' => 'NOT LIKE']);
            \App\Relation::create(['name' => 'Üres', 'symbol' => 'IS NULL']);
            \App\Relation::create(['name' => 'Nem üres', 'symbol' => 'IS NOT NULL']);
        }
    }
}
