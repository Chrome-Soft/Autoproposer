<?php

use Illuminate\Database\Seeder;

class LanguageTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $this->call(CurrencyTableSeeder::class);

        $languages = \App\Language::all();
        if ($languages->isEmpty()) {
            \App\Language::create(['name' => 'Magyar', 'code' => 'HU', 'currency_id' => \App\Currency::where('code', 'FT')->first()->id]);
            \App\Language::create(['name' => 'Angol', 'code' => 'EN', 'currency_id' => \App\Currency::where('code', 'GBP')->first()->id]);
            \App\Language::create(['name' => 'Német', 'code' => 'GE', 'currency_id' => \App\Currency::where('code', 'EUR')->first()->id]);
        }
    }
}
