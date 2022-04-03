<?php

use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     *
     * @return void
     */
    public function run()
    {
        \Illuminate\Support\Facades\Artisan::call('cache:clear');

        $this->call(ProposerItemTypeTableSeeder::class);
        $this->call(LanguageTableSeeder::class);

        $this->call(ProductAttributeTableSeeder::class);
        $this->call(UserTableSeeder::class);

        $this->call(CriteriaTableSeeder::class);
        $this->call(RelationTableSeeder::class);

        $this->call(SegmentProductPriorityTableSeeder::class);

        if (\Illuminate\Support\Facades\App::environment('local')) {
//            $this->call(PageLoadTableSeeder::class);
//            $this->call(UserDataInteractionSeeder::class);
//            $this->call(PartnerTableSeeder::class);
        }
    }
}
