<?php

namespace Tests\Feature;

use App\Criteria;
use App\Currency;
use App\PageLoad;
use App\Product;
use App\ProductAttributeType;
use App\ProductProductAttribute;
use App\Relation;
use App\Segment;
use App\SegmentGroup;
use App\SegmentGroupCriteria;
use App\Services\Recommender\Fallback\PopularProductFallback;
use App\UserData;
use Illuminate\Foundation\Testing\DatabaseMigrations;
use Illuminate\Foundation\Testing\WithoutMiddleware;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Foundation\Testing\RefreshDatabase;

class PopularProductFallbackTest extends TestCase
{
    use DatabaseMigrations;

    protected function setUp(): void
    {
        parent::setUp();
        $this->withExceptionHandling();
    }

    /** @test */
    public function it_returns_the_most_popular_n_products()
    {
        // 3 product, 2 kérünk, 2 népszerű jön vissza
        $segment = create('Segment');

        $productMostPopular = create('Product');
        $productMidPopular = create('Product');
        $productLeastPopular = create('Product');

        // 2 buy a legnépszerűbb termékhez
        $interaction1 = create('Interaction', 1, ['type' => 'buy']);
        $interaction2 = create('Interaction', 1, ['type' => 'buy']);
        $interactionItem1 = create('InteractionItem', 1, ['interaction_id' => $interaction1->id,  'item_id' => $productMostPopular->id, 'item_type' => Product::class]);
        $interactionItem2 = create('InteractionItem', 1, ['interaction_id' => $interaction2->id,  'item_id' => $productMostPopular->id, 'item_type' => Product::class]);

        // 1 view a közepes termékhez
        $interaction3 = create('Interaction', 1, ['type' => 'view']);
        $interactionItem3 = create('InteractionItem', 1, ['interaction_id' => $interaction3->id,  'item_id' => $productMidPopular->id, 'item_type' => Product::class]);

        $fallback = new PopularProductFallback;
        $products = $fallback->getProducts($segment, 2, []);

        $this->assertCount(2, $products);
        $this->assertEquals(
            [$productMostPopular->id, $productMidPopular->id],
            $products->pluck('id')->all()
        );
    }

    /** @test */
    public function it_excludes_given_products()
    {
        // 3 product, 2 kérünk, csak a közepesen népszerű jön vissza, mert a többit kizárjuk
        $segment = create('Segment');

        $productMostPopular = create('Product');
        $productMidPopular = create('Product');
        $productLeastPopular = create('Product');

        // 2 buy a legnépszerűbb termékhez. DE EZ EXCLUDED TERMÉK LESZ
        $interaction1 = create('Interaction', 1, ['type' => 'buy']);
        $interaction2 = create('Interaction', 1, ['type' => 'buy']);
        $interactionItem1 = create('InteractionItem', 1, ['interaction_id' => $interaction1->id,  'item_id' => $productMostPopular->id, 'item_type' => Product::class]);
        $interactionItem2 = create('InteractionItem', 1, ['interaction_id' => $interaction2->id,  'item_id' => $productMostPopular->id, 'item_type' => Product::class]);

        // 1 view a közepes termékhez
        $interaction3 = create('Interaction', 1, ['type' => 'view']);
        $interactionItem3 = create('InteractionItem', 1, ['interaction_id' => $interaction3->id,  'item_id' => $productMidPopular->id, 'item_type' => Product::class]);

        $fallback = new PopularProductFallback;
        $products = $fallback->getProducts($segment, 2, [$productMostPopular->id, $productLeastPopular->id]);

        $this->assertCount(1, $products);
        $this->assertEquals(
            [$productMidPopular->id],
            $products->pluck('id')->all()
        );
    }
}
