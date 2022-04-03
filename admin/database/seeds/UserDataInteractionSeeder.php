<?php

use Illuminate\Database\Seeder;

class UserDataInteractionSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        for ($i = 0; $i < 500; $i++) {
            $userData = factory(\App\UserData::class)->create([
                'user_id'   => \Ramsey\Uuid\Uuid::uuid4()->toString(),
            ]);

            $x = rand(0,10);
            for ($j = 0; $j < $x; $j++) {
                $interaction = factory(\App\Interaction::class)->create([
                     'cookie_id'    => $userData->cookie_id,
                     'user_id'      => $userData->user_id,
                ]);

                $y = rand(1,5);
                for ($k = 1; $k <= $y; $k++) {
                    $interactionItem = factory(\App\InteractionItem::class)->create([
                        'interaction_id'    => $interaction->id,
                        'buy_quantity'      => $interaction->type == 'buy' ? rand(1,10) : null,
                        'buy_unit_price'    => $interaction->type == 'buy' ? rand(4990,9900) : null
                    ]);
                }
            }
        }
    }
}
