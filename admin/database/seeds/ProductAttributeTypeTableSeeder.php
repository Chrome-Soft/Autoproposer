<?php

use Illuminate\Database\Seeder;

class ProductAttributeTypeTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $types = \App\ProductAttributeType::all();
        if ($types->isEmpty()) {
            \App\ProductAttributeType::create(['name' => 'Szöveg', 'properties' => [
                'numberOfElems'     => 1,
                'elemType'          => 'textarea',
                'classes'           => [],
            ]]);
            \App\ProductAttributeType::create(['name' => 'Szám', 'properties' => [
                'numberOfElems'     => 1,
                'elemType'          => 'input',
                'inputType'         => 'number',
                'classes'           => []
            ]]);
            \App\ProductAttributeType::create(['name' => 'Igen/Nem', 'properties' => [
                'numberOfElems'     => 1,
                'elemType'          => 'input',
                'inputType'         => 'checkbox',
                'classes'           => []
            ]]);
            \App\ProductAttributeType::create(['name' => 'Dátum', 'properties' => [
                'numberOfElems'     => 1,
                'elemType'          => 'input',
                'inputType'         => 'text',
                'classes'           => ['datepicker']
            ]]);
            \App\ProductAttributeType::create(['name' => 'Dátum intervallum', 'properties' => [
                'numberOfElems'     => 2,
                'elemType'          => 'input',
                'inputType'         => 'text',
                'classes'           => ['datepicker']
            ]]);
        }

        $datetime = \App\ProductAttributeType::where('name', 'Dátum idő')->first();
        if (!$datetime) {
            \App\ProductAttributeType::create(['name' => 'Dátum idő', 'properties' => [
                'numberOfElems'     => 1,
                'elemType'          => 'input',
                'inputType'         => 'text',
                'classes'           => ['datetimepicker']
            ]]);
        }

        $datetimeInterval = \App\ProductAttributeType::where('name', 'Dátum idő intervallum')->first();
        if (!$datetimeInterval) {
            \App\ProductAttributeType::create(['name' => 'Dátum idő intervallum', 'properties' => [
                'numberOfElems'     => 2,
                'elemType'          => 'input',
                'inputType'         => 'text',
                'classes'           => ['datetimepicker']
            ]]);
        }
    }
}
