<?php

namespace Tests\Feature;

use App\Currency;
use App\PageLoad;
use App\Product;
use App\ProductAttribute;
use App\ProductAttributeType;
use App\ProductProductAttribute;
use App\UserData;
use Illuminate\Foundation\Testing\DatabaseMigrations;
use Illuminate\Foundation\Testing\WithoutMiddleware;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Foundation\Testing\RefreshDatabase;

class ProductUpdateTest extends TestCase
{
    use DatabaseMigrations;

    protected function setUp(): void
    {
        parent::setUp();
        $this->withExceptionHandling();

        $this->artisan('db:seed', ['--class' => 'CurrencyTableSeeder']);
        $this->artisan('db:seed', ['--class' => 'ProductAttributeTableSeeder']);
    }

    /** @test */
    public function update_fail_if_attribute_id_invalid()
    {
        $this->signIn();

        $product = create('Product', 1, [
            'user_id'   => auth()->id(),
            'name'      => 'Product 1'
        ]);
        create('ProductPrice', 1, [
            'product_id'    => $product->id,
            'currency_id'   => Currency::where('code', 'FT')->first()->id
        ]);

        $data = [
            'name'          => 'Termek 1',
            'description'   => 'asdf',
            'attribute_ids' => [-1],
            'prices'        => [
                0   => 100,
            ],
            'currencies'    => [
                0   => Currency::where('code', 'FT')->first()->id,
            ]
        ];
        $response = $this->patch($product->path(), $data);
        $response->assertSessionHasErrors(['attribute_ids']);
        $response->assertStatus(302);

        $this->assertCount(0, $product->attributes);
    }

    /** @test */
    public function update_existing_simple_attribute()
    {
        $this->signIn();

        $product = create('Product', 1, [
            'user_id'   => auth()->id(),
            'name'      => 'Product 1'
        ]);
        create('ProductPrice', 1, [
            'product_id'    => $product->id,
            'currency_id'   => Currency::where('code', 'FT')->first()->id
        ]);

        $attributeId = ProductAttribute::where('slug', 'magassag')->first()->id;
        $product->attributes()->attach($attributeId, [
            'value' => 20
        ]);

        $data = [
            'name'          => 'Termek 1',
            'description'   => 'asdf',
            'attribute_ids' => [$attributeId],
            'attribute_values'  => [25],
            'prices'        => [
                0   => 100,
            ],
            'currencies'    => [
                0   => Currency::where('code', 'FT')->first()->id,
            ]
        ];
        $response = $this->patch($product->path(), $data);
        $response->assertSessionHasNoErrors();
        $response->assertStatus(302);

        $this->assertCount(1, $product->attributes);
        $this->assertEquals(25, $product->attributes()->first()->pivot->value);
    }

    /** @test */
    public function update_existing_extra_attribute()
    {
        $this->signIn();

        $product = create('Product', 1, [
            'user_id'   => auth()->id(),
            'name'      => 'Product 1'
        ]);
        create('ProductPrice', 1, [
            'product_id'    => $product->id,
            'currency_id'   => Currency::where('code', 'FT')->first()->id
        ]);

        $attributeId = ProductAttribute::where('slug', 'ervenyessegi-intervallum')->first()->id;
        $product->attributes()->attach($attributeId, [
            'value' => json_encode([
                '2019.01.05', '2019.01.10'
            ])
        ]);

        $data = [
            'name'          => 'Termek 1',
            'description'   => 'asdf',
            'attribute_ids' => [$attributeId],
            'attribute_values'  => ['2020.01.05', '2020.01.10'],
            'prices'        => [
                0   => 100,
            ],
            'currencies'    => [
                0   => Currency::where('code', 'FT')->first()->id,
            ]
        ];
        $response = $this->patch($product->path(), $data);
        $response->assertSessionHasNoErrors();
        $response->assertStatus(302);

        $this->assertCount(1, $product->attributes);
        $this->assertEquals(['2020.01.05', '2020.01.10'], $product->attributes()->first()->pivot->value);
    }

    /** @test */
    public function update_add_one_more_attribute()
    {
        $this->signIn();

        $product = create('Product', 1, [
            'user_id'   => auth()->id(),
            'name'      => 'Product 1'
        ]);
        create('ProductPrice', 1, [
            'product_id'    => $product->id,
            'currency_id'   => Currency::where('code', 'FT')->first()->id
        ]);

        $attributeId = ProductAttribute::where('slug', 'ervenyessegi-intervallum')->first()->id;
        $product->attributes()->attach($attributeId, [
            'value' => json_encode([
                '2019.01.05', '2019.01.10'
            ])
        ]);

        $newAttributeId = ProductAttribute::where('slug', 'magassag')->first()->id;

        $data = [
            'name'          => 'Termek 1',
            'description'   => 'asdf',
            'attribute_ids' => [$attributeId, $newAttributeId],
            'attribute_values'  => ['2020.01.05', '2020.01.10', 35],
            'prices'        => [
                0   => 100,
            ],
            'currencies'    => [
                0   => Currency::where('code', 'FT')->first()->id,
            ]
        ];
        $response = $this->patch($product->path(), $data);
        $response->assertSessionHasNoErrors();
        $response->assertStatus(302);

        $this->assertCount(2, $product->attributes);
        $this->assertEquals(['2020.01.05', '2020.01.10'], $product->attributes()->where('product_attribute_id', $attributeId)->first()->pivot->value);
        $this->assertEquals(35, $product->attributes()->where('product_attribute_id', $newAttributeId)->first()->pivot->value);
    }

    /** @test */
    public function update_add_multiple_attribute()
    {
        $this->signIn();

        $product = create('Product', 1, [
            'user_id'   => auth()->id(),
            'name'      => 'Product 1'
        ]);
        create('ProductPrice', 1, [
            'product_id'    => $product->id,
            'currency_id'   => Currency::where('code', 'FT')->first()->id
        ]);

        $attributeId = ProductAttribute::where('slug', 'ervenyessegi-intervallum')->first()->id;
        $product->attributes()->attach($attributeId, [
            'value' => json_encode([
                '2019.01.05', '2019.01.10'
            ])
        ]);

        $newAttributeId1 = ProductAttribute::where('slug', 'magassag')->first()->id;
        $newAttributeId2 = ProductAttribute::where('slug', 'ervenyessegi-ido')->first()->id;

        $data = [
            'name'          => 'Termek 1',
            'description'   => 'asdf',
            'attribute_ids' => [$attributeId, $newAttributeId1, $newAttributeId2],
            'attribute_values'  => ['2020.01.05', '2020.01.10', 35, 72],
            'prices'        => [
                0   => 100,
            ],
            'currencies'    => [
                0   => Currency::where('code', 'FT')->first()->id,
            ]
        ];
        $response = $this->patch($product->path(), $data);
        $response->assertSessionHasNoErrors();
        $response->assertStatus(302);

        $this->assertCount(3, $product->attributes);
        $this->assertEquals(['2020.01.05', '2020.01.10'], $product->attributes()->where('product_attribute_id', $attributeId)->first()->pivot->value);
        $this->assertEquals(35, $product->attributes()->where('product_attribute_id', $newAttributeId1)->first()->pivot->value);
        $this->assertEquals(72, $product->attributes()->where('product_attribute_id', $newAttributeId2)->first()->pivot->value);
    }

    /** @test */
    public function update_remove_one_attribute()
    {
        $this->signIn();

        $product = create('Product', 1, [
            'user_id'   => auth()->id(),
            'name'      => 'Product 1'
        ]);
        create('ProductPrice', 1, [
            'product_id'    => $product->id,
            'currency_id'   => Currency::where('code', 'FT')->first()->id
        ]);

        $attributeId = ProductAttribute::where('slug', 'ervenyessegi-intervallum')->first()->id;
        $product->attributes()->attach($attributeId, [
            'value' => json_encode([
                '2019.01.05', '2019.01.10'
            ])
        ]);

        $data = [
            'name'          => 'Termek 1',
            'description'   => 'asdf',
            'prices'        => [
                0   => 100,
            ],
            'currencies'    => [
                0   => Currency::where('code', 'FT')->first()->id,
            ]
        ];
        $response = $this->patch($product->path(), $data);
        $response->assertSessionHasNoErrors();
        $response->assertStatus(302);

        $this->assertCount(0, $product->attributes);
    }

    /** @test */
    public function update_remove_multiple_attribute()
    {
        $this->signIn();

        $product = create('Product', 1, [
            'user_id'   => auth()->id(),
            'name'      => 'Product 1'
        ]);
        create('ProductPrice', 1, [
            'product_id'    => $product->id,
            'currency_id'   => Currency::where('code', 'FT')->first()->id
        ]);

        $attributeId1 = ProductAttribute::where('slug', 'ervenyessegi-intervallum')->first()->id;
        $attributeId2 = ProductAttribute::where('slug', 'ervenyessegi-ido')->first()->id;
        $attributeId3 = ProductAttribute::where('slug', 'magassag')->first()->id;
        $product->attributes()->attach($attributeId1, [
            'value' => json_encode([
                '2019.01.05', '2019.01.10'
            ])
        ]);
        $product->attributes()->attach($attributeId2, [
            'value' => 24
        ]);
        $product->attributes()->attach($attributeId3, [
            'value' => 200
        ]);

        $data = [
            'name'          => 'Termek 1',
            'description'   => 'asdf',
            'attribute_ids' => [$attributeId3],
            'attribute_values'  => [300],
            'prices'        => [
                0   => 100,
            ],
            'currencies'    => [
                0   => Currency::where('code', 'FT')->first()->id,
            ]
        ];
        $response = $this->patch($product->path(), $data);
        $response->assertSessionHasNoErrors();
        $response->assertStatus(302);

        $this->assertCount(1, $product->attributes);
        $this->assertEquals(300, $product->attributes()->first()->pivot->value);
    }

    /** @test */
    public function update_remove_multiple_add_multiple_attribute()
    {
        $this->signIn();

        $product = create('Product', 1, [
            'user_id'   => auth()->id(),
            'name'      => 'Product 1'
        ]);
        create('ProductPrice', 1, [
            'product_id'    => $product->id,
            'currency_id'   => Currency::where('code', 'FT')->first()->id
        ]);

        $attributeId1 = ProductAttribute::where('slug', 'ervenyessegi-intervallum')->first()->id;
        $attributeId2 = ProductAttribute::where('slug', 'ervenyessegi-ido')->first()->id;
        $attributeId3 = ProductAttribute::where('slug', 'magassag')->first()->id;
        $attributeId4 = ProductAttribute::where('slug', 'szelesseg')->first()->id;
        $product->attributes()->attach($attributeId1, [
            'value' => json_encode([
                '2019.01.05', '2019.01.10'
            ])
        ]);
        $product->attributes()->attach($attributeId2, [
            'value' => 24
        ]);

        $data = [
            'name'          => 'Termek 1',
            'description'   => 'asdf',
            'attribute_ids' => [$attributeId3, $attributeId4],
            'attribute_values'  => [300, 200],
            'prices'        => [
                0   => 100,
            ],
            'currencies'    => [
                0   => Currency::where('code', 'FT')->first()->id,
            ]
        ];
        $response = $this->patch($product->path(), $data);
        $response->assertSessionHasNoErrors();
        $response->assertStatus(302);

        $this->assertCount(2, $product->attributes);
        $this->assertEquals(300, $product->attributes()->where('product_attribute_id', $attributeId3)->first()->pivot->value);
        $this->assertEquals(200, $product->attributes()->where('product_attribute_id', $attributeId4)->first()->pivot->value);
    }

    /** @test */
    public function update_prices_and_photos()
    {
        $this->signIn();

        $product = create('Product', 1, [
            'user_id'   => auth()->id(),
            'name'      => 'Product 1'
        ]);
        create('ProductPrice', 1, [
            'product_id'    => $product->id,
            'currency_id'   => Currency::where('code', 'FT')->first()->id
        ]);
        create('ProductPrice', 1, [
            'product_id'    => $product->id,
            'currency_id'   => Currency::where('code', 'EUR')->first()->id
        ]);
        create('ProductPhoto', 2, [
            'product_id'    => $product->id
        ]);

        $photo = UploadedFile::fake()->image('image-1.jpg');

        $data = [
            'name'          => 'Termek 1',
            'description'   => 'asdf',
            'prices'        => [
                0   => 100,
            ],
            'currencies'    => [
                0   => Currency::where('code', 'FT')->first()->id,
            ],
            'photos'        => [$photo]
        ];
        $response = $this->patch($product->path(), $data);
        $response->assertSessionHasNoErrors();
        $response->assertStatus(302);

        $this->assertCount(1, $product->prices);
        $this->assertCount(2, $product->photos);

        $pathSmall = 'products/small-' . $photo->hashName();
        $pathMedium = 'products/medium-' . $photo->hashName();
        Storage::disk('public')->assertExists($pathSmall);
        Storage::disk('public')->assertExists($pathMedium);
    }

    /** @test */
    public function remove_photo()
    {
        $this->signIn();

        $product = create('Product', 1, [
            'user_id'   => auth()->id(),
            'name'      => 'Product 1'
        ]);
        $photo = create('ProductPhoto', 1, [
            'product_id'    => $product->id
        ]);

        $response = $this->delete("/products/{$product->slug}/photos/{$photo->id}");
        $response->assertSessionHasNoErrors();
        $response->assertStatus(200);

        $this->assertCount(0, $product->photos);
    }

    /** @test */
    public function regression_it_does_not_remove_photo_when_no_new_photo_selected()
    {
        $this->signIn();

        $product = create('Product', 1, [
            'user_id'   => auth()->id(),
            'name'      => 'Product 1'
        ]);
        $photo = create('ProductPhoto', 1, [
            'product_id'    => $product->id
        ]);

        $data = [
            'name'          => 'Termek 1',
            'description'   => 'asdf',
            'prices'        => [
                0   => 100,
            ],
            'currencies'    => [
                0   => Currency::where('code', 'FT')->first()->id,
            ]
        ];

        $response = $this->patch($product->path(), $data);
        $response->assertSessionHasNoErrors();
        $response->assertStatus(302);

        $this->assertCount(1, $product->photos);
    }
}
