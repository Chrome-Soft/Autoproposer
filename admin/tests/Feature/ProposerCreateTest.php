<?php

namespace Tests\Feature;

use App\ApiKey;
use App\Http\Controllers\Api\RecommendationController;
use App\PageLoad;
use App\ProductPhoto;
use App\ProposerItem;
use App\ProposerItemType;
use App\Services\Recommender\IRecommenderService;
use App\Services\Recommender\RecommenderService;
use App\UserData;
use Illuminate\Foundation\Testing\DatabaseMigrations;
use Illuminate\Foundation\Testing\WithoutMiddleware;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Storage;

class ProposerCreateTest extends TestCase
{
    use DatabaseMigrations;

    protected function setUp(): void
    {
        parent::setUp();
        $this->withExceptionHandling();

        $this->artisan('db:seed', ['--class' => 'ProposerItemTypeTableSeeder']);
    }

    /** @test */
    public function partner_id_not_exists()
    {
        $this->signIn();
        $partner = create('Partner');

        $data = [
            'partner_id'        => 'asdf',      // <- invalid
            'name'              => 'Proposer 1',
            'width'             => 720,
            'height'            => 500,
            'page_url'          => '',
            'max_item_number'   => 5,
            'description'       => 'description'
        ];

        $response = $this->post('/proposers', $data);
        $response->assertSessionHasErrors('partner_id');

        $this->assertDatabaseMissing('proposers', [
            'name'  => 'Proposer 1'
        ]);
    }

    /** @test */
    public function max_item_number_out_of_range()
    {
        $this->signIn();
        $partner = create('Partner');

        $data = [
            'partner_id'        => $partner->id,
            'name'              => 'Proposer 1',
            'width'             => 720,
            'height'            => 500,
            'page_url'          => '',
            'max_item_number'   => 10000,
            'description'       => 'description'
        ];

        $response = $this->post('/proposers', $data);
        $response->assertSessionHasErrors('max_item_number');

        $this->assertDatabaseMissing('proposers', [
            'name'  => 'Proposer 1'
        ]);
    }

    /** @test */
    public function create()
    {
        $this->signIn();
        $partner = create('Partner');

        $data = [
            'partner_id'        => $partner->id,      // <- invalid
            'name'              => 'Proposer 1',
            'width'             => 720,
            'height'            => 500,
            'page_url'          => '/info',
            'max_item_number'   => 5,
            'description'       => 'description',
            'type_id'           => 2
        ];

        $response = $this->post('/proposers', $data);
        $response->assertSessionHasNoErrors();
        $response->assertStatus(302);

        $this->assertDatabaseHas('proposers', [
            'name'  => 'Proposer 1'
        ]);
    }

    /** @test */
    public function update()
    {
        $this->signIn();
        $proposer = create('Proposer', 1, [
            'user_id'   => auth()->id()
        ]);

        $proposer->name = 'Proposer 1';

        $response = $this->patch($proposer->path(), $proposer->toArray());
        $response->assertSessionHasNoErrors();
        $response->assertStatus(302);

        $this->assertDatabaseHas('proposers', [
            'id'    => $proposer->id,
            'name'  => 'Proposer 1',
            'slug'  => 'proposer-1'
        ]);
    }

    /** @test */
    public function it_removes_slashes_from_url_when_creating()
    {
        $this->signIn();
        $partner = create('Partner');

        $data = [
            'partner_id'        => $partner->id,      // <- invalid
            'name'              => 'Proposer 1',
            'width'             => 720,
            'height'            => 500,
            'page_url'          => '/info/',
            'max_item_number'   => 5,
            'description'       => 'description',
            'type_id'           => 2
        ];

        $response = $this->post('/proposers', $data);
        $response->assertSessionHasNoErrors();
        $response->assertStatus(302);

        $this->assertDatabaseHas('proposers', [
            'name'      => 'Proposer 1',
            'page_url'  => 'info'
        ]);
    }

    /** @test */
    public function it_removes_slashes_from_url_when_updating()
    {
        $this->signIn();
        $proposer = create('Proposer', 1, [
            'user_id'   => auth()->id()
        ]);

        $proposer->name = 'Proposer 1';
        $proposer->page_url = '/rolunk/';

        $response = $this->patch($proposer->path(), $proposer->toArray());
        $response->assertSessionHasNoErrors();
        $response->assertStatus(302);

        $this->assertDatabaseHas('proposers', [
            'id'        => $proposer->id,
            'name'      => 'Proposer 1',
            'slug'      => 'proposer-1',
            'page_url'  => 'rolunk'
        ]);
    }

    /** @test */
    public function it_returns_data_for_recommendation()
    {
        $this->signIn();
        $proposer = create('Proposer');

        $product = create('Product', 1, [
            'link'  => 'http://product.com'
        ]);
        $photo = new ProductPhoto;
        $photo->product_id = $product->id;
        $photo->user_id = $this->signedInUser->id;
        $photo->image_path = 'path/to/image.jpg';
        $photo->save();

        $proposerItem = new ProposerItem;
        $proposerItem->proposer_id = $proposer->id;
        $proposerItem->user_id = $this->signedInUser->id;
        $proposerItem->type_id = ProposerItemType::where('key', ProposerItemType::TYPE_PRODUCT)->first()->id;
        $proposerItem->product_id = $product->id;
        $proposerItem->save();

        Cache::shouldReceive('get')
            ->times(1)
            ->with('proposer_item_types')
            ->andReturn(ProposerItemType::all());

        $result = $proposer->getItemsWithLabels()->first();

        $this->assertEquals('product', $result->type_key);
        $this->assertEquals('http://product.com', $result->link);
        $this->assertNotNull($result->thumbnail_photos);
    }

    /** @test */
    public function it_throws_403_if_api_not_valid_for_iframe()
    {
        $partner = create('Partner', 1, [
            'name'  => 'partner'
        ]);

        $proposer = create('Proposer', 1, [
            'partner_id' => $partner->id
        ]);

        $response = $this->get('proposers/' . $proposer->slug . '/recommendations/cookie_id');
        $response->assertStatus(403);
    }

    /** @test */
    public function it_stores_interactions_when_iframe_requested()
    {
        $serviceMock = $this->createMock(RecommenderService::class);
        $serviceMock->method('waitForSegment')
            ->willReturn(create('Segment'));

        $serviceMock->method('recommend')
            ->willReturn([
                ['id' => 1, 'name' => 'first', 'type_key' => 'product'],
                ['id' => 2, 'name' => 'second', 'type_key' => 'product']
            ]);

        $this->app->instance(IRecommenderService::class, $serviceMock);

        $partner = create('Partner', 1, [
            'name'  => 'partner'
        ]);

        $proposer = create('Proposer', 1, [
            'partner_id' => $partner->id
        ]);

        $segment = create('Segment');
        $userData = create('UserData', 1, ['segment_id' => $segment->id]);

        $response = $this->get('/proposers/' . $proposer->slug . '/recommendations/' . $userData->cookie_id . '?api_key=' . $partner->apiKey->key);
        $response->assertStatus(200);

        $this->assertDatabaseHas('interactions', [
            'type'      => 'present',
            'cookie_id' => $userData->cookie_id
        ]);

        $this->assertDatabaseHas('interaction_items', [
            'item_id'   => 1,
            'item_name' => 'first'
        ]);
        $this->assertDatabaseHas('interaction_items', [
            'item_id'   => 2,
            'item_name' => 'second'
        ]);
    }

    /** @test */
    public function it_requires_page_url_if_type_embedded()
    {
        $this->signIn();
        $partner = create('Partner');

        $data = [
            'partner_id'        => $partner->id,
            'name'              => 'Proposer 1',
            'max_item_number'   => 5,
            'description'       => 'description',
            'type_id'           => 2
        ];

        $response = $this->post('/proposers', $data);
        $response->assertSessionHasErrors(['page_url']);

        $this->assertDatabaseMissing('proposers', [
            'name'  => 'Proposer 1'
        ]);
    }

    /** @test */
    public function it_requires_width_height_if_type_iframe()
    {
        $this->signIn();
        $partner = create('Partner');

        $data = [
            'partner_id'        => $partner->id,
            'name'              => 'Proposer 1',
            'max_item_number'   => 5,
            'description'       => 'description',
            'type_id'           => 1
        ];

        $response = $this->post('/proposers', $data);
        $response->assertSessionHasErrors(['width', 'height']);

        $this->assertDatabaseMissing('proposers', [
            'name'  => 'Proposer 1'
        ]);
    }
}
