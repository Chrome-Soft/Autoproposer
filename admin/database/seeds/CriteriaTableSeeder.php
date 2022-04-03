<?php

use Illuminate\Database\Seeder;

class CriteriaTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $criterias = \App\Criteria::all();
        // TODO vannak olyan kritériumok, amik migrációból jönnek. Ez éles rendszernél nem okoz gondot, hiszen ez a seed
        // csak az elején kell, hogy lefusson egyszer. Viszont teszteknél problémát okozhat.
        if ($criterias->count() <= 4) {
            \App\Criteria::create(['name' => 'Eszköz gyártó', 'slug' => 'device_manufacturer']);
            \App\Criteria::create(['name' => 'Eszköz megnevezése', 'slug' => 'device_product']);
            \App\Criteria::create(['name' => 'Mobil?', 'slug' => 'device_is_mobile']);
            \App\Criteria::create(['name' => 'Eszköz memória', 'slug' => 'device_memory']);
            \App\Criteria::create(['name' => 'Felbontás szélessége', 'slug' => 'device_screen_width']);
            \App\Criteria::create(['name' => 'Felbontás magassága', 'slug' => 'device_screen_height']);

            \App\Criteria::create(['name' => 'Operációs rendszer architektúra (32bit, 64bit)', 'slug' => 'os_architecture']);
            \App\Criteria::create(['name' => 'Operációs rendszer típusa', 'slug' => 'os_name']);
            \App\Criteria::create(['name' => 'Operációs rendszer verziója', 'slug' => 'os_version']);

            \App\Criteria::create(['name' => 'Böngésző', 'slug' => 'browser_name']);
            \App\Criteria::create(['name' => 'Böngésző verziója', 'slug' => 'browser_version']);
            \App\Criteria::create(['name' => 'Böngésző nyelve', 'slug' => 'browser_language']);

            \App\Criteria::create(['name' => 'Sávszélesség', 'slug' => 'connection_bandwidth']);
            \App\Criteria::create(['name' => 'IP cím', 'slug' => 'connection_ip_address']);

            \App\Criteria::create(['name' => 'Ország', 'slug' => 'location_country_name']);
            \App\Criteria::create(['name' => 'Város', 'slug' => 'location_city_name']);
            \App\Criteria::create(['name' => 'Irányítószám', 'slug' => 'location_postal_code']);
            \App\Criteria::create(['name' => 'Megye', 'slug' => 'location_subdivision_name']);

            \App\Criteria::create(['name' => 'E-mail domain', 'slug' => 'email_domain']);
            \App\Criteria::create(['name' => 'Mobil szolgáltató', 'slug' => 'phone_prodiver']);
            \App\Criteria::create(['name' => 'Születési év', 'slug' => 'birth_date']);
        }
    }
}
