<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\DatabaseMigrations;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\URL;

class ProductPhotoTest extends TestCase
{
    use DatabaseMigrations;

    protected function setUp(): void
    {
        parent::setUp();
        $this->withExceptionHandling();
    }

    /** @test */
    public function it_has_thumbnails_with_public_url_to_all_sizes()
    {
        $product = create('Product');

        $photoSmall = create('ProductPhoto', 1, ['product_id' => $product->id, 'image_path' => 'small-path.jpg']);
        $photoMedium = create('ProductPhoto', 1, ['product_id' => $product->id, 'image_path' => 'medium-path.jpg']);
        $photoLarge = create('ProductPhoto', 1, ['product_id' => $product->id, 'image_path' => 'large-path.jpg']);

        URL::shouldReceive('to')
            ->with('/')
            ->andReturn('base.url');

        Storage::shouldReceive('url')
            ->andReturnUsing(function ($x) {
                return '/storage/' . $x;
            });

        $thumbnails = $product->thumbnail_photos;
        $this->assertEquals('base.url/storage/small-path.jpg', $thumbnails['small']->public_url);
        $this->assertEquals('base.url/storage/medium-path.jpg', $thumbnails['medium']->public_url);
        $this->assertEquals('base.url/storage/large-path.jpg', $thumbnails['large']->public_url);

        $this->assertEquals('/storage/small-path.jpg', $thumbnails['small']->public_path);
        $this->assertEquals('/storage/medium-path.jpg', $thumbnails['medium']->public_path);
        $this->assertEquals('/storage/large-path.jpg', $thumbnails['large']->public_path);

        $this->assertEquals('small-path.jpg', $thumbnails['small']->image_path);
        $this->assertEquals('medium-path.jpg', $thumbnails['medium']->image_path);
        $this->assertEquals('large-path.jpg', $thumbnails['large']->image_path);
    }

    /** @test */
    public function it_has_thumbnails_with_public_path_to_all_sizes()
    {
        $product = create('Product');

        $photoSmall = create('ProductPhoto', 1, ['product_id' => $product->id, 'image_path' => 'small-path.jpg']);
        $photoMedium = create('ProductPhoto', 1, ['product_id' => $product->id, 'image_path' => 'medium-path.jpg']);
        $photoLarge = create('ProductPhoto', 1, ['product_id' => $product->id, 'image_path' => 'large-path.jpg']);

        URL::shouldReceive('to')
            ->with('/')
            ->andReturn('base.url');

        Storage::shouldReceive('url')
            ->andReturnUsing(function ($x) {
                return '/storage/' . $x;
            });

        $thumbnails = $product->thumbnail_photos;
        $this->assertEquals('/storage/small-path.jpg', $thumbnails['small']->public_path);
        $this->assertEquals('/storage/medium-path.jpg', $thumbnails['medium']->public_path);
        $this->assertEquals('/storage/large-path.jpg', $thumbnails['large']->public_path);

        $this->assertEquals('small-path.jpg', $thumbnails['small']->image_path);
        $this->assertEquals('medium-path.jpg', $thumbnails['medium']->image_path);
        $this->assertEquals('large-path.jpg', $thumbnails['large']->image_path);
    }

    /** @test */
    public function it_has_thumbnails_with_image_path_to_all_sizes()
    {
        $product = create('Product');

        $photoSmall = create('ProductPhoto', 1, ['product_id' => $product->id, 'image_path' => 'small-path.jpg']);
        $photoMedium = create('ProductPhoto', 1, ['product_id' => $product->id, 'image_path' => 'medium-path.jpg']);
        $photoLarge = create('ProductPhoto', 1, ['product_id' => $product->id, 'image_path' => 'large-path.jpg']);

        URL::shouldReceive('to')
            ->with('/')
            ->andReturn('base.url');

        Storage::shouldReceive('url')
            ->andReturnUsing(function ($x) {
                return '/storage/' . $x;
            });

        $thumbnails = $product->thumbnail_photos;
        $this->assertEquals('small-path.jpg', $thumbnails['small']->image_path);
        $this->assertEquals('medium-path.jpg', $thumbnails['medium']->image_path);
        $this->assertEquals('large-path.jpg', $thumbnails['large']->image_path);
    }

    /** @test */
    public function it_has_external_thumbnails_url_if_image_comes_from_url()
    {
        $product = create('Product');

        $photoSmall = create('ProductPhoto', 1, ['product_id' => $product->id, 'image_path' => 'http://image.com/small-path.jpg']);
        $photoMedium = create('ProductPhoto', 1, ['product_id' => $product->id, 'image_path' => 'http://image.com/medium-path.jpg']);
        $photoLarge = create('ProductPhoto', 1, ['product_id' => $product->id, 'image_path' => 'http://image.com/large-path.jpg']);

        $thumbnails = $product->thumbnail_photos;
        $this->assertEquals('http://image.com/small-path.jpg', $thumbnails['small']->public_url);
        $this->assertEquals('http://image.com/medium-path.jpg', $thumbnails['medium']->public_url);
        $this->assertEquals('http://image.com/large-path.jpg', $thumbnails['large']->public_url);

        $this->assertEquals('http://image.com/small-path.jpg', $thumbnails['small']->public_path);
        $this->assertEquals('http://image.com/medium-path.jpg', $thumbnails['medium']->public_path);
        $this->assertEquals('http://image.com/large-path.jpg', $thumbnails['large']->public_path);

        $this->assertEquals('http://image.com/small-path.jpg', $thumbnails['small']->image_path);
        $this->assertEquals('http://image.com/medium-path.jpg', $thumbnails['medium']->image_path);
        $this->assertEquals('http://image.com/large-path.jpg', $thumbnails['large']->image_path);
    }
}
