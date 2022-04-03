<?php

use Illuminate\Database\Seeder;

class ProductAttributeTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $this->call(ProductAttributeTypeTableSeeder::class);

        $attributes = \App\ProductAttribute::all();
        if ($attributes->isEmpty()) {
            \App\ProductAttribute::create(['name' => 'Magasság', 'slug' => \Illuminate\Support\Str::slug('Magasság'), 'type_id' => App\ProductAttributeType::where('name', 'Szám')->first()->id]);
            \App\ProductAttribute::create(['name' => 'Szélesség', 'slug' => \Illuminate\Support\Str::slug('Szélesség'), 'type_id' => App\ProductAttributeType::where('name', 'Szám')->first()->id]);
            \App\ProductAttribute::create(['name' => 'Érvényességi idő', 'slug' => \Illuminate\Support\Str::slug('Érvényességi idő'), 'type_id' => App\ProductAttributeType::where('name', 'Szám')->first()->id]);
            \App\ProductAttribute::create(['name' => 'Érvényességi intervallum', 'slug' => \Illuminate\Support\Str::slug('Érvényességi intervallum'), 'type_id' => App\ProductAttributeType::where('name', 'Dátum intervallum')->first()->id]);
            \App\ProductAttribute::create(['name' => 'Vásárlási intervallum', 'slug' => \Illuminate\Support\Str::slug('Vásárlási intervallum'), 'type_id' => App\ProductAttributeType::where('name', 'Dátum intervallum')->first()->id]);
        }

        $availableIntervalTime = \App\ProductAttribute::where('name', 'Érvényességi intervallum időponttal')->first();
        if (!$availableIntervalTime) {
            \App\ProductAttribute::create(['name' => 'Érvényességi intervallum időponttal', 'slug' => \Illuminate\Support\Str::slug('Érvényességi intervallum időponttal'), 'type_id' => App\ProductAttributeType::where('name', 'Dátum idő intervallum')->first()->id]);
        }

        $buyIntervalTime = \App\ProductAttribute::where('name', 'Vásárlási intervallum időponttal')->first();
        if (!$buyIntervalTime) {
            \App\ProductAttribute::create(['name' => 'Vásárlási intervallum időponttal', 'slug' => \Illuminate\Support\Str::slug('Vásárlási intervallum időponttal'), 'type_id' => App\ProductAttributeType::where('name', 'Dátum idő intervallum')->first()->id]);
        }

        $cardDiscount = \App\ProductAttribute::where('name', 'Kedvezmény')->first();
        if (!$cardDiscount) {
            \Illuminate\Support\Facades\DB::table('product_attributes')->insert([
                'name'      => 'Kedvezmény',
                'slug'      => \App\ProductAttribute::DISCOUNT_SLUG,
                'type_id'   => App\ProductAttributeType::where('name', 'Szám')->first()->id
            ]);
        }
    }
}
