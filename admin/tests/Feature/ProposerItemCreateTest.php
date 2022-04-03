<?php

namespace Tests\Feature;

use App\PageLoad;
use App\Proposer;
use App\ProposerItem;
use App\ProposerItemType;
use App\UserData;
use Illuminate\Foundation\Testing\DatabaseMigrations;
use Illuminate\Foundation\Testing\WithoutMiddleware;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Foundation\Testing\RefreshDatabase;

class ProposerItemCreateTest extends TestCase
{
    use DatabaseMigrations;

    protected function setUp(): void
    {
        parent::setUp();
        $this->withExceptionHandling();

        $this->artisan('db:seed', ['--class' => 'ProposerItemTypeTableSeeder']);
    }

    /** @test */
    public function fail_if_type_html_but_content_empty()
    {
        $this->signIn();
        $proposer = create('Proposer');

        $data = [
            'proposer_id'   => $proposer->id,
            'type'          => ProposerItemType::TYPE_HTML,
            'html_content'  => null,
            'image_url'     => null,
            'product_id'    => -1
        ];

        $response = $this->post("/proposers/{$proposer->slug}/items", $data);
        $response->assertSessionHasErrors('html_content');

        $this->assertDatabaseMissing('proposer_items', [
            'proposer_id'  => $proposer->id
        ]);
    }

    /** @test */
    public function create_with_html_content()
    {
        $this->signIn();
        $proposer = create('Proposer');

        $data = [
            'proposer_id'   => $proposer->id,
            'type'          => ProposerItemType::TYPE_HTML,
            'html_content'  => "<html>blahblah",
            'image_url'     => null,
            'product_id'    => -1
        ];

        $response = $this->post("/proposers/{$proposer->slug}/items", $data);
        $response->assertSessionHasNoErrors();

        $this->assertDatabaseHas('proposer_items', [
            'proposer_id'  => $proposer->id
        ]);
    }

    /** @test */
    public function fail_if_type_image_but_content_empty()
    {
        $this->signIn();
        $proposer = create('Proposer');

        $data = [
            'proposer_id'   => $proposer->id,
            'type'          => ProposerItemType::TYPE_IMAGE,
            'html_content'  => null,
            'image'         => null,
            'product_id'    => -1
        ];

        $response = $this->post("/proposers/{$proposer->slug}/items", $data);
        $response->assertSessionHasErrors('image');

        $this->assertDatabaseMissing('proposer_items', [
            'proposer_id'  => $proposer->id
        ]);
    }

    /** @test */
    public function fail_if_type_image_but_not_valid()
    {
        $this->signIn();
        $proposer = create('Proposer');

        $data = [
            'proposer_id'   => $proposer->id,
            'type'          => ProposerItemType::TYPE_IMAGE,
            'html_content'  => null,
            'image'         => 'this is not an image',
            'product_id'    => -1
        ];

        $response = $this->post("/proposers/{$proposer->slug}/items", $data);
        $response->assertSessionHasErrors('image');

        $this->assertDatabaseMissing('proposer_items', [
            'proposer_id'  => $proposer->id
        ]);
    }

    /** @test */
    public function fail_if_type_image_but_size_too_large()
    {
        $this->signIn();
        $proposer = create('Proposer');

        Storage::fake('public');
        $file = UploadedFile::fake()->image('image.jpg');
        $file->size(26354);

        $data = [
            'proposer_id'   => $proposer->id,
            'type'          => ProposerItemType::TYPE_IMAGE,
            'html_content'  => null,
            'image'         => $file,
            'product_id'    => -1
        ];

        $response = $this->post("/proposers/{$proposer->slug}/items", $data);
        $response->assertSessionHasErrors('image');

        $this->assertDatabaseMissing('proposer_items', [
            'proposer_id'  => $proposer->id
        ]);
    }

    /** @test */
    public function create_with_image()
    {
        $this->signIn();
        $proposer = create('Proposer');

        Storage::fake('public');
        $file = UploadedFile::fake()->image('image.jpg');
        $file->size(567);

        $data = [
            'proposer_id'   => $proposer->id,
            'type'          => ProposerItemType::TYPE_IMAGE,
            'html_content'  => null,
            'image'         => $file,
            'product_id'    => -1
        ];

        $response = $this->post("/proposers/{$proposer->slug}/items", $data);
        $response->assertSessionHasNoErrors();

        $this->assertDatabaseHas('proposer_items', [
            'proposer_id'  => $proposer->id
        ]);

        $pathSmall = 'proposer-items/small-' . $file->hashName();
        $pathMedium = 'proposer-items/medium-' . $file->hashName();
        Storage::disk('public')->assertExists($pathSmall);
        Storage::disk('public')->assertExists($pathMedium);

        $proposerItem = ProposerItem::where('proposer_id', $proposer->id)->first();
        $this->assertEquals($proposerItem->small_photo->image_path, $pathSmall);
        $this->assertEquals($proposerItem->medium_photo->image_path, $pathMedium);
    }

    /** @test */
    public function fail_if_type_product_but_content_empty()
    {
        $this->signIn();
        $proposer = create('Proposer');

        $data = [
            'proposer_id'   => $proposer->id,
            'type'          => ProposerItemType::TYPE_PRODUCT,
            'html_content'  => null,
            'image_url'     => null,
            'product_id'    => -1
        ];

        $response = $this->post("/proposers/{$proposer->slug}/items", $data);
        $response->assertSessionHasErrors('product_id');

        $this->assertDatabaseMissing('proposer_items', [
            'proposer_id'  => $proposer->id
        ]);
    }

    /** @test */
    public function fail_if_type_product_but_not_exists()
    {
        $this->signIn();
        $proposer = create('Proposer');

        $data = [
            'proposer_id'   => $proposer->id,
            'type'          => ProposerItemType::TYPE_PRODUCT,
            'html_content'  => null,
            'image_url'     => null,
            'product_id'    => 244234
        ];

        $response = $this->post("/proposers/{$proposer->slug}/items", $data);
        $response->assertSessionHasErrors('product_id');

        $this->assertDatabaseMissing('proposer_items', [
            'proposer_id'  => $proposer->id
        ]);
    }

    /** @test */
    public function create_with_product()
    {
        $this->signIn();
        $proposer = create('Proposer');
        $product = create('Product');

        $data = [
            'proposer_id'   => $proposer->id,
            'type'          => ProposerItemType::TYPE_PRODUCT,
            'html_content'  => null,
            'image_url'     => null,
            'product_id'    => $product->id
        ];

        $response = $this->post("/proposers/{$proposer->slug}/items", $data);
        $response->assertSessionHasNoErrors();

        $this->assertDatabaseHas('proposer_items', [
            'proposer_id'  => $proposer->id
        ]);
    }
    
    /** @test */
    public function fail_if_number_of_items_exceed_max_items()
    {
        $this->signIn();
        $proposer = create('Proposer', 1, [
            'user_id'           => auth()->id(),
            'max_item_number'   => 2
        ]);

        $data = [
            'proposer_id'   => $proposer->id,
            'type'          => ProposerItemType::TYPE_HTML,
            'html_content'  => 'HTML content',
        ];

        $response = $this->post("/proposers/{$proposer->slug}/items", $data);
        $response->assertSessionHasNoErrors();
        $response->assertStatus(302);
        $proposer = Proposer::find($proposer->id)->withCount('items')->first();
        $this->assertEquals(1, $proposer->items_count);

        $response = $this->post("/proposers/{$proposer->slug}/items", $data);
        $response->assertSessionHasNoErrors();
        $proposer = Proposer::find($proposer->id)->withCount('items')->first();
        $this->assertEquals(2, $proposer->items_count);

        $response = $this->post("/proposers/{$proposer->slug}/items", $data);
        $response->assertSessionHasErrors(['proposer_id']);
        $proposer = Proposer::find($proposer->id)->withCount('items')->first();
        $this->assertEquals(2, $proposer->items_count);
    }

    /** @test */
    public function can_update_without_max_item_validation()
    {
        $this->signIn();
        $proposer = create('Proposer', 1, [
            'user_id'           => auth()->id(),
            'max_item_number'   => 1
        ]);

        $item = create('ProposerItem', 1, [
            'proposer_id'   => $proposer->id,
            'type_id'        => ProposerItemType::where('key', ProposerItemType::TYPE_HTML)->first()->id,
            'html_content'  => 'HTML content'
        ]);

        $item->html_content = 'content 2';

        $response = $this->patch("/proposers/{$proposer->slug}/items", $item->toArray());
        $response->assertSessionHasNoErrors();
    }

    /** @test */
    public function update()
    {
        $this->signIn();
        $proposer = create('Proposer', 1, [
            'user_id'   => auth()->id()
        ]);
        $proposerItem = create('ProposerItem', 1, [
            'user_id'       => auth()->id(),
            'type_id'       => ProposerItemType::where('key', ProposerItemType::TYPE_HTML)->first()->id,
            'html_content'  => "<html>content</html>html>",
            'proposer_id'   => $proposer->id
        ]);

        $proposerItem->html_content = 'New html content';
        $proposerItem->type = ProposerItemType::TYPE_HTML;

        $response = $this->patch($proposerItem->path(), $proposerItem->toArray());
        $response->assertSessionHasNoErrors();
        $response->assertStatus(302);

        $this->assertDatabaseHas('proposer_items', [
            'id'            => $proposerItem->id,
            'html_content'  => 'New html content'
        ]);
    }
}
