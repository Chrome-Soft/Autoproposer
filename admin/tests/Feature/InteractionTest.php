<?php

namespace Tests\Feature;

use App\Interaction;
use App\PageLoad;
use App\Product;
use App\ProposerItem;
use App\UserData;
use Illuminate\Foundation\Testing\DatabaseMigrations;
use Illuminate\Foundation\Testing\WithFaker;
use Illuminate\Foundation\Testing\WithoutMiddleware;
use Illuminate\Support\Facades\DB;
use Illuminate\Foundation\Testing\RefreshDatabase;

class InteractionTest extends TestCase
{
    use DatabaseMigrations, WithoutMiddleware, WithFaker;

    /** @test */
    public function partner_id_not_exists()
    {
        $partner = create('Partner' , 1, [
            'external_id'   =>  \Ramsey\Uuid\Uuid::uuid4()->toString()
        ]);

        $data = [
            'partnerId'     => 'asdf',      // <- invalid
            'cookie_id'     => 'asdf'
        ];

        $response = $this->post('/api/interaction', $data);
        $response->assertStatus(422);

        $this->assertArrayHasKey('partnerId', json_decode($response->getContent(), true));
    }

    /** @test */
    public function cookie_id_empty()
    {
        $partner = create('Partner' , 1);

        $data = [
            'partnerId'     => $partner->external_id,
        ];

        $response = $this->post('/api/interaction', $data);
        $response->assertStatus(422);

        $this->assertArrayHasKey('cookieId', json_decode($response->getContent(), true));
    }

    /** @test */
    public function invalid_type()
    {
        $partner = create('Partner' , 1);

        $data = [
            'partnerId'     => $partner->external_id,
            'cookieId'      => $this->faker->sha1,
            'type'          => 'dasdf'
        ];

        $response = $this->post('/api/interaction', $data);
        $response->assertStatus(422);

        $this->assertArrayHasKey('type', json_decode($response->getContent(), true));
    }

    /** @test */
    public function no_user_id_when_type_is_buy()
    {
        $partner = create('Partner' , 1);

        $data = [
            'partnerId'     => $partner->external_id,
            'cookieId'      => $this->faker->sha1,
            'type'          => 'buy'
        ];

        $response = $this->post('/api/interaction', $data);
        $response->assertStatus(422);

        $this->assertArrayHasKey('userId', json_decode($response->getContent(), true));
    }

    /** @test */
    public function invalid_items_when_type_is_buy()
    {
        $partner = create('Partner' , 1);

        $data = [
            'partnerId'     => $partner->external_id,
            'cookieId'      => $this->faker->sha1,
            'type'          => 'buy',
            'userId'        => 'asdf',
            'items'         => [
                ['id' => 1, 'name' => 'First', 'qty' => 1, 'unitPrice' => 1990],
                ['id' => 2, 'name' => 'Second'],     // <- invalid
            ]
        ];

        $response = $this->post('/api/interaction', $data);
        $response->assertStatus(422);

        $this->assertArrayHasKey('items.1.qty', json_decode($response->getContent(), true));
        $this->assertArrayHasKey('items.1.unitPrice', json_decode($response->getContent(), true));
    }

    /** @test */
    public function store()
    {
        $partner = create('Partner' , 1);

        $data = [
            'partnerId'     => $partner->external_id,
            'cookieId'      => $this->faker->sha1,
            'type'          => 'buy',
            'userId'        => 'asdf',
            'items'         => [
                ['id' => 1, 'name' => 'First', 'type' => 'product', 'qty' => 1, 'unitPrice' => 1990],
                ['id' => 2, 'name' => 'Second', 'type' => 'product', 'qty' => 2 , 'unitPrice' => 2990]
            ]
        ];

        $response = $this->post('/api/interaction', $data);
        $response->assertStatus(200);

        $this->assertDatabaseHas('interactions', [
            'user_id'   => 'asdf',
            'type'      => 'buy'
        ]);

        $interaction = Interaction::first();
        $this->assertDatabaseHas('interaction_items', [
            'interaction_id'    => $interaction->id,
            'item_id'           => 1,
            'item_name'         => 'First',
            'buy_quantity'      => 1,
            'buy_unit_price'    => 1990
        ]);
        $this->assertDatabaseHas('interaction_items', [
            'interaction_id'    => $interaction->id,
            'item_id'           => 2,
            'item_name'         => 'Second',
            'buy_quantity'      => 2,
            'buy_unit_price'    => 2990
        ]);
    }

    /** @test */
    public function it_map_types()
    {
        $type = Interaction::mapType(['name' => 'Product', 'type' => 'product']);
        $this->assertEquals(Product::class, $type);

        $type = Interaction::mapType(['name' => null]);
        $this->assertEquals(ProposerItem::class, $type);

        $type = Interaction::mapType([]);
        $this->assertEquals(ProposerItem::class, $type);
    }
}
