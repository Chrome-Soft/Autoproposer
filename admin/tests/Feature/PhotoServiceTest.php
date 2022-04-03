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
use App\Services\PhotoService;
use App\Services\PhotoSize;
use App\UserData;
use Illuminate\Foundation\Testing\DatabaseMigrations;
use Illuminate\Foundation\Testing\WithoutMiddleware;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Intervention\Image\Facades\Image;

class PhotoServiceTest extends TestCase
{
    use DatabaseMigrations;

    protected function setUp(): void
    {
        parent::setUp();
        $this->withExceptionHandling();

        $this->artisan('db:seed', ['--class' => 'CriteriaTableSeeder']);
        $this->artisan('db:seed', ['--class' => 'RelationTableSeeder']);
    }

    /** @test */
    public function it_uploads_given_photos_and_link_to_model()
    {
        $service = new PhotoService;

        $this->signIn();
        $product = create('Product');

        $photo = UploadedFile::fake()->image('image-1.jpg');
        Storage::fake('public');

        $service->uploadPhotos($product, [$photo], $this->signedInUser->id);

        $pathSmall = 'products/small-' . $photo->hashName();
        $pathMedium = 'products/medium-' . $photo->hashName();

        Storage::disk('public')->assertExists($pathSmall);
        Storage::disk('public')->assertExists($pathMedium);

        $this->assertDatabaseHas('product_photos', [
            'product_id'    => $product->id,
            'image_path'    => $pathSmall
        ]);
        $this->assertDatabaseHas('product_photos', [
            'product_id'    => $product->id,
            'image_path'    => $pathMedium
        ]);
    }

    /** @test */
    public function it_removes_all_previous_photos_if_any_for_model()
    {
        $service = new PhotoService;

        $this->signIn();
        $product = create('Product');
        $photoSmall = create('ProductPhoto', 1, ['product_id' => $product->id, 'image_path' => 'http://image.com/small-path.jpg']);

        $photo = UploadedFile::fake()->image('image-1.jpg');
        Storage::fake('public');

        $service->uploadPhotos($product, [$photo], $this->signedInUser->id);

        $this->assertDatabaseMissing('product_photos', [
            'product_id'    => $product->id,
            'image_path'    => 'http://image.com/small-path.jpg'
        ]);

        $pathSmall = 'products/small-' . $photo->hashName();
        $pathMedium = 'products/medium-' . $photo->hashName();

        Storage::disk('public')->assertExists($pathSmall);
        Storage::disk('public')->assertExists($pathMedium);

        $this->assertDatabaseHas('product_photos', [
            'product_id'    => $product->id,
            'image_path'    => $pathSmall
        ]);
        $this->assertDatabaseHas('product_photos', [
            'product_id'    => $product->id,
            'image_path'    => $pathMedium
        ]);
    }

    /** @test */
    public function it_uploads_multiple_photos_and_link_to_model()
    {
        $service = new PhotoService;

        $this->signIn();
        $product = create('Product');

        $photo1 = UploadedFile::fake()->image('image-1.jpg');
        $photo2 = UploadedFile::fake()->image('image-2.jpg');
        Storage::fake('public');

        $service->uploadPhotos($product, [$photo1, $photo2], $this->signedInUser->id);

        $pathSmall1 = 'products/small-' . $photo1->hashName();
        $pathMedium1 = 'products/medium-' . $photo1->hashName();
        $pathSmall2 = 'products/small-' . $photo2->hashName();
        $pathMedium2 = 'products/medium-' . $photo2->hashName();

        Storage::disk('public')->assertExists($pathSmall1);
        Storage::disk('public')->assertExists($pathMedium1);
        Storage::disk('public')->assertExists($pathSmall2);
        Storage::disk('public')->assertExists($pathMedium2);

        $this->assertDatabaseHas('product_photos', [
            'product_id'    => $product->id,
            'image_path'    => $pathSmall1
        ]);
        $this->assertDatabaseHas('product_photos', [
            'product_id'    => $product->id,
            'image_path'    => $pathMedium1
        ]);
        $this->assertDatabaseHas('product_photos', [
            'product_id'    => $product->id,
            'image_path'    => $pathSmall2
        ]);
        $this->assertDatabaseHas('product_photos', [
            'product_id'    => $product->id,
            'image_path'    => $pathMedium2
        ]);
    }

    /** @test */
    public function it_uploads_a_photo_with_multiple_sizes()
    {
        $service = new PhotoService;

        $photo = UploadedFile::fake()->image('image-1.jpg');
        Storage::fake('public');

        $pathSmall = 'products/small-' . $photo->hashName();
        $pathMedium = 'products/medium-' . $photo->hashName();

        $service->uploadMultipleSizes($photo, 'products');

        Storage::disk('public')->assertExists($pathSmall);
        Storage::disk('public')->assertExists($pathMedium);
    }

    /** @test */
    public function it_uploads_a_photo_with_given_multiple_sizes()
    {
        $service = new PhotoService;

        $photo = UploadedFile::fake()->image('image-1.jpg');
        Storage::fake('public');

        $pathSmall = 'products/small-' . $photo->hashName();
        $pathMedium = 'products/medium-' . $photo->hashName();
        $pathLarge = 'products/large-' . $photo->hashName();

        $service->uploadMultipleSizes($photo, 'products', new PhotoSize(10, 20, 30));

        Storage::disk('public')->assertExists($pathSmall);
        Storage::disk('public')->assertExists($pathMedium);
        Storage::disk('public')->assertExists($pathLarge);
    }

    /** @test */
    public function it_does_not_remove_images_if_empty_array_given()
    {
        $service = new PhotoService;

        $this->signIn();
        $product = create('Product');
        $photoSmall = create('ProductPhoto', 1, ['product_id' => $product->id, 'image_path' => 'http://image.com/small-path.jpg']);

        $photo = UploadedFile::fake()->image('image-1.jpg');
        Storage::fake('public');

        $service->uploadPhotos($product, [], $this->signedInUser->id);

        $this->assertDatabaseHas('product_photos', [
            'product_id'    => $product->id,
            'image_path'    => 'http://image.com/small-path.jpg'
        ]);
    }
}
