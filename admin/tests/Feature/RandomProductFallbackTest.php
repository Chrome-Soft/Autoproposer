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
use App\Services\Recommender\Fallback\RandomProductFallback;
use App\UserData;
use Illuminate\Foundation\Testing\DatabaseMigrations;
use Illuminate\Foundation\Testing\WithoutMiddleware;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Foundation\Testing\RefreshDatabase;

class RandomProductFallbackTest extends TestCase
{
    use DatabaseMigrations;

    protected function setUp(): void
    {
        parent::setUp();
        $this->withExceptionHandling();
    }

    /** @test */
    public function it_does_not_return_excluded_products()
    {
        $segment = create('Segment');

        $product1 = create('Product');
        $product2 = create('Product');
        $product3 = create('Product');

        $fallback = new RandomProductFallback;
        $products = $fallback->getProducts($segment, 2, [$product3->id]);

        $this->assertCount(2, $products);
        $this->assertEquals(
            [$product1->id, $product2->id],
            $products->pluck('id')->all()
        );
    }

    /** @test */
    public function it_includes_product_type_label()
    {
        $segment = create('Segment');

        $product1 = create('Product');
        $product2 = create('Product');
        $product3 = create('Product');

        $fallback = new RandomProductFallback;
        $products = $fallback->getProducts($segment, 2, [$product3->id]);

        $this->assertCount(2, $products);
        foreach ($products as $product) {
            $this->assertEquals('product', $product->type_key);
        }
    }
}
