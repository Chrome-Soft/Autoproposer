<?php

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class ProposerItemTypeTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $types = \App\ProposerItemType::all();
        if ($types->isEmpty()) {
            \App\ProposerItemType::create(['name' => 'HTML tartalom', 'key' => \App\ProposerItemType::TYPE_HTML]);
            \App\ProposerItemType::create(['name' => 'Kép feltöltése', 'key' => \App\ProposerItemType::TYPE_IMAGE]);
            \App\ProposerItemType::create(['name' => 'Termék kiválasztása', 'key' => \App\ProposerItemType::TYPE_PRODUCT]);
        }
    }
}
