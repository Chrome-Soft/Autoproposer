<?php

use Illuminate\Database\Seeder;

class CurrencyTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $currencies = \App\Language::all();
        if ($currencies->isEmpty()) {
            \App\Currency::create(['name' => 'Magyar forint', 'code' => 'FT', 'symbol' => 'Ft']);
            \App\Currency::create(['name' => 'Angol font', 'code' => 'GBP', 'symbol' => '£']);
            \App\Currency::create(['name' => 'Euró', 'code' => 'EUR', 'symbol' => '€']);
        }
    }
}
