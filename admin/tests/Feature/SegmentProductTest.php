<?php

namespace Tests\Feature;

use App\Currency;
use App\PageLoad;
use App\Product;
use App\ProductAttributeType;
use App\ProductProductAttribute;
use App\ProposerItemType;
use App\Segment;
use App\SegmentGroup;
use App\SegmentGroupCriteria;
use App\SegmentProduct;
use App\SegmentProductPriority;
use App\Services\HttpClient;
use App\Services\Recommender\Fallback\FallbackFactory;
use App\Services\Recommender\RecommenderService;
use App\UserData;
use Illuminate\Foundation\Testing\DatabaseMigrations;
use Illuminate\Foundation\Testing\WithoutMiddleware;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Foundation\Testing\RefreshDatabase;

class SegmentProductTest extends TestCase
{
    use DatabaseMigrations;

    protected function setUp(): void
    {
        parent::setUp();
        $this->withExceptionHandling();

        $this->artisan('db:seed', ['--class' => 'SegmentProductPriorityTableSeeder']);
    }

    /** @test */
    public function add_product()
    {
        $user = create('User');
        $this->actingAs($user, 'api');

        $segment = create('Segment');
        $product = create('Product');

        $response = $this->post('/api/segment-products', [
            'productId'     => $product->id,
            'priorityId'    => SegmentProductPriority::ALWAYS_PRESENT,
            'segmentId'     => $segment->id
        ]);

        $response->assertStatus(200);
        $this->assertCount(1, $segment->products);

        $products = Product::getAllExcept($segment);
        $this->assertNull($products->where('id', $product->id)->first());
    }

    /** @test */
    public function remove_product()
    {
        $user = create('User');
        $this->actingAs($user, 'api');

        $segment = create('Segment', 1, ['user_id' => $user->id]);
        $product = create('Product');
        $product2 = create('Product');

        $segmentProduct = new SegmentProduct;
        $segmentProduct->segment_id = $segment->id;
        $segmentProduct->product_id = $product->id;
        $segmentProduct->priority_id = SegmentProductPriority::ALWAYS_PRESENT;
        $segmentProduct->user_id = auth()->id();
        $segmentProduct->save();

        $segmentProduct2 = new SegmentProduct;
        $segmentProduct2->segment_id = $segment->id;
        $segmentProduct2->product_id = $product2->id;
        $segmentProduct2->priority_id = SegmentProductPriority::ALWAYS_PRESENT;
        $segmentProduct2->user_id = auth()->id();
        $segmentProduct2->save();

        $response = $this->delete("/api/segment-products/{$segmentProduct->id}");

        $response->assertStatus(200);
        $this->assertCount(1, $segment->products);

        $products = Product::getAllExcept($segment);
        $this->assertNotNull($products->where('id', $product->id)->first());
        $this->assertNull($products->where('id', $product2->id)->first());
    }

    /** @test */
    public function it_returns_a_collection_with_custom_products()
    {
        $segment = create('Segment');

        $product1 = create('Product', 1, ['name' => 'Segment Product Always Presents']);
        $this->createSegmentProduct($segment, $product1, SegmentProductPriority::ALWAYS_PRESENT);

        $product2 = create('Product', 1, ['name' => 'Segment Product Optional Presents']);
        $this->createSegmentProduct($segment, $product2, SegmentProductPriority::OPTIONAL_PRESENT);

        $clientMock = $this->createMock(HttpClient::class);
        $recommenderService = new RecommenderService($clientMock, new FallbackFactory, 'http://base.url');

        $products = $segment->getProductsByType();

        $product1->priority = 'always';
        $this->assertEquals(
            array_merge(
                $product1->getAttributes(), ['type_key' => ProposerItemType::TYPE_PRODUCT]
            ), $products['always']->first()->getAttributes());

        $product2->priority = 'optional';
        $this->assertEquals(
            array_merge(
                $product2->getAttributes(), ['type_key' =>ProposerItemType::TYPE_PRODUCT]
            ), $products['optional']->first()->getAttributes());
    }

    protected function createSegmentProduct(Segment $segment, Product $product, $priority)
    {
        $segmentProduct = new SegmentProduct;
        $segmentProduct->segment_id = $segment->id;
        $segmentProduct->product_id = $product->id;
        $segmentProduct->priority_id = $priority;
        $segmentProduct->user_id = $this->signedInUser->id;
        $segmentProduct->save();

        return $segmentProduct;
    }
}
