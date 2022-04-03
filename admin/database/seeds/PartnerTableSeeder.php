<?php

use Illuminate\Database\Seeder;

class PartnerTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        for ($i = 0; $i < 10; $i++) {
            $partner = factory(\App\Partner::class)->create();
            $apiKey = new \Ejarnutowski\LaravelApiKey\Models\ApiKey;
            $apiKey->name = $partner->slug;
            $apiKey->partner_id = $partner->id;
            $apiKey->key = \Ejarnutowski\LaravelApiKey\Models\ApiKey::generate();

            $apiKey->save();

            $x = rand(0,10);
            for ($j = 0; $j < $x; $j++) {
                $proposer = factory(\App\Proposer::class)->create([
                    'partner_id'    => $partner->id,
                    'user_id'       => $partner->user_id
                ]);

                $y = rand(0,3);
                for ($k = 0; $k < $y; $k++) {
                    factory(\App\ProposerItem::class)->create([
                        'proposer_id'   => $proposer->id,
                        'user_id'       => $partner->user_id
                    ])
                    ->each(function ($x) {
                        if (count($x->product->prices) == 0) {
                            $x->product->prices()->save(factory(App\ProductPrice::class)->make([
                                'product_id'    => $x->product->id,
                                'currency_id'   => App\Currency::first()->id
                            ]));
                        }
                    });
                }
            }
        }
    }
}
