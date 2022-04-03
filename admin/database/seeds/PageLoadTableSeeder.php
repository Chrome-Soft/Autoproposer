<?php

use Illuminate\Database\Seeder;

class PageLoadTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        for ($i = 0; $i < 1000; $i++) {
            factory(\App\PageLoad::class)->create();
        }
    }
}
