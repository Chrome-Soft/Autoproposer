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
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Foundation\Testing\RefreshDatabase;

class ProductCreateTest extends TestCase
{
    use DatabaseMigrations;

    protected function setUp(): void
    {
        parent::setUp();
        $this->withExceptionHandling();

        $this->artisan('db:seed', ['--class' => 'CurrencyTableSeeder']);
        $this->artisan('db:seed', ['--class' => 'ProductAttributeTypeTableSeeder']);
        $this->artisan('db:seed', ['--class' => 'ProductAttributeTableSeeder']);
    }

    /** @test */
    public function fail_if_price_null()
    {
        $this->signIn();

        $data = [
            'name'          => 'Termék 1',
            'description'   => 'asdf'
        ];

        $response = $this->post('/products', $data);
        $response->assertSessionHasErrors('prices');

        $this->assertDatabaseMissing('products', [
            'name'  => 'Termék 1'
        ]);
    }

    /** @test */
    public function fail_if_no_price_given()
    {
        $this->signIn();

        $data = [
            'name'          => 'Termék 1',
            'description'   => 'asdf',
            'prices'        => []
        ];

        $response = $this->post('/products', $data);
        $response->assertSessionHasErrors('prices');

        $this->assertDatabaseMissing('products', [
            'name'  => 'Termék 1'
        ]);
    }

    /** @test */
    public function create_multiple_prices()
    {
        $this->signIn();

        $data = [
            'name'          => 'Termek 1',
            'description'   => 'asdf',
            'prices'        => [
                0   => 100,
                1   => 200
            ],
            'currencies'    => [
                0   => Currency::where('code', 'FT')->first()->id,
                1   => Currency::where('code', 'EUR')->first()->id
            ]
        ];

        $response = $this->post('/products', $data);
        $response->assertSessionHasNoErrors();
        $response->assertStatus(302);

        $this->assertDatabaseHas('products', [
            'name'  => 'Termek 1'
        ]);

        $product = Product::where('name', 'Termek 1')->first();
        $this->assertCount(2, $product->prices);
    }

    /** @test */
    public function create_multiple_prices_filter_out_null_values()
    {
        $this->signIn();

        $data = [
            'name'          => 'Termek 1',
            'description'   => 'asdf',
            'prices'        => [
                0   => 100,
                1   => null
            ],
            'currencies'    => [
                0   => Currency::where('code', 'FT')->first()->id,
                1   => Currency::where('code', 'EUR')->first()->id
            ]
        ];

        $response = $this->post('/products', $data);
        $response->assertSessionHasNoErrors();
        $response->assertStatus(302);

        $this->assertDatabaseHas('products', [
            'name'  => 'Termek 1'
        ]);

        $product = Product::where('name', 'Termek 1')->first();
        $this->assertCount(1, $product->prices);
    }

    /** @test */
    public function create_multiple_photos()
    {
        $this->signIn();
        Storage::fake('public');

        $photo1 = UploadedFile::fake()->image('image-1.jpg');
        $photo2 = UploadedFile::fake()->image('image-2.jpg');

        $data = [
            'name'          => 'Termek 1',
            'description'   => 'asdf',
            'prices'        => [
                0   => 100,
                1   => 200
            ],
            'currencies'    => [
                0   => Currency::where('code', 'FT')->first()->id,
                1   => Currency::where('code', 'EUR')->first()->id
            ],
            'photos' => [$photo1, $photo2]
        ];

        $response = $this->post('/products', $data);
        $response->assertSessionHasNoErrors();
        $response->assertStatus(302);

        $this->assertDatabaseHas('products', [
            'name'  => 'Termek 1'
        ]);

        $product = Product::where('name', 'Termek 1')->first();
        $this->assertCount(4, $product->photos);

        $path1Small = 'products/small-' . $photo1->hashName();
        $path1Medium = 'products/small-' . $photo1->hashName();
        Storage::disk('public')->assertExists($path1Small);
        Storage::disk('public')->assertExists($path1Medium);

        $path2Small = 'products/small-' . $photo2->hashName();
        $path2Medium = 'products/medium-' . $photo2->hashName();
        Storage::disk('public')->assertExists($path2Small);
        Storage::disk('public')->assertExists($path2Medium);
    }

    /** @test */
    public function create_fail_if_attribute_id_invalid()
    {
        $this->signIn();

        $data = [
            'name'          => 'Termek 1',
            'description'   => 'asdf',
            'prices'        => [
                0   => 100
            ],
            'currencies'    => [
                0   => Currency::where('code', 'FT')->first()->id
            ],
            'attribute_ids' => [-1]
        ];

        $response = $this->post('/products', $data);
        $response->assertSessionHasErrors(['attribute_ids']);

        $this->assertDatabaseMissing('products', [
            'name'  => 'Termek 1'
        ]);
    }

    /** @test */
    public function create_with_multiple_attributes()
    {
        $this->signIn();

        $attributes = create('ProductAttribute', 2, [
            'type_id'   => ProductAttributeType::TYPE_TEXT
        ]);

        $data = [
            'name'              => 'Termek 1',
            'description'       => 'asdf',
            'prices'            => [
                0   => 100
            ],
            'currencies'        => [
                0   => Currency::where('code', 'FT')->first()->id
            ],
            'attribute_ids'     => $attributes->map(function ($x) { return $x->id; })->toArray(),
            'attribute_values'  => ['first', 'second']
        ];

        $response = $this->post('/products', $data);
        $response->assertSessionHasNoErrors();

        $this->assertDatabaseHas('products', [
            'name'  => 'Termek 1'
        ]);

        $product = Product::where('name', 'Termek 1')->first();
        $this->assertCount(2, $product->attributes);
    }

    /** @test */
    public function create_with_multiple_attributes_checkbox_is_middle_with_unchecked()
    {
        $this->signIn();

        $attribute1 = create('ProductAttribute', 1, [
            'type_id'   => ProductAttributeType::TYPE_TEXT
        ]);
        $checkboxAttr = create('ProductAttribute', 1, [
            'type_id'   => ProductAttributeType::TYPE_BOOL
        ]);
        $attribute2 = create('ProductAttribute', 1, [
            'type_id'   => ProductAttributeType::TYPE_TEXT
        ]);

        $data = [
            'name'              => 'Termek 1',
            'description'       => 'asdf',
            'prices'            => [
                0   => 100
            ],
            'currencies'        => [
                0   => Currency::where('code', 'FT')->first()->id
            ],
            'attribute_ids'     => [$attribute1->id, $checkboxAttr->id, $attribute2->id],
            'attribute_values'  => ['first', 'off', 'second']
        ];

        $response = $this->post('/products', $data);
        $response->assertSessionHasNoErrors();

        $this->assertDatabaseHas('products', [
            'name'  => 'Termek 1'
        ]);

        $product = Product::where('name', 'Termek 1')->first();
        $this->assertCount(3, $product->attributes);

        $prodAttr1 = $product->attributes()->where('product_attribute_id', $attribute1->id)->first();
        $this->assertEquals('first', $prodAttr1->pivot->value);

        $prodAttr2 = $product->attributes()->where('product_attribute_id', $attribute2->id)->first();
        $this->assertEquals('second', $prodAttr2->pivot->value);

        // value accessor miatt a model value mezője 'Nem' értéket ad vissza
        $prodAttrCheck = DB::table('product_product_attribute')->where(['product_id' => $product->id, 'product_attribute_id' => $checkboxAttr->id])->first();
        $this->assertEquals('off', $prodAttrCheck->value);
    }

    /** @test */
    public function create_with_multiple_attributes_middle_has_two_inputs()
    {
        $this->signIn();

        $attribute1 = create('ProductAttribute', 1, [
            'type_id'   => ProductAttributeType::TYPE_NUMBER
        ]);
        $attribute2 = create('ProductAttribute', 1, [
            'type_id'   => ProductAttributeType::TYPE_DATE_INTERVAL
        ]);
        $attribute3 = create('ProductAttribute', 1, [
            'type_id'   => ProductAttributeType::TYPE_TEXT
        ]);

        $data = [
            'name'              => 'Termek 1',
            'description'       => 'asdf',
            'prices'            => [
                0   => 100
            ],
            'currencies'        => [
                0   => Currency::where('code', 'FT')->first()->id
            ],
            'attribute_ids'     => [$attribute1->id, $attribute2->id, $attribute3->id],
            'attribute_values'  => ['20', '2019-01-05', '2019-01-15', 'text']
        ];

        $response = $this->post('/products', $data);
        $response->assertSessionHasNoErrors();

        $this->assertDatabaseHas('products', [
            'name'  => 'Termek 1'
        ]);

        $product = Product::where('name', 'Termek 1')->first();
        $this->assertCount(3, $product->attributes);

        $prodAttr1 = $product->attributes()->where('product_attribute_id', $attribute1->id)->first();
        $this->assertEquals('20', $prodAttr1->pivot->value);

        $prodAttr2 = $product->attributes()->where('product_attribute_id', $attribute2->id)->first();
        $this->assertEquals([
            '2019-01-05', '2019-01-15'
        ], $prodAttr2->pivot->value);

        $prodAttr3 = $product->attributes()->where('product_attribute_id', $attribute3->id)->first();
        $this->assertEquals('text', $prodAttr3->pivot->value);
    }

    /** @test */
    public function create_with_multiple_attributes_first_has_two_inputs()
    {
        $this->signIn();

        $attribute1 = create('ProductAttribute', 1, [
            'type_id'   => ProductAttributeType::TYPE_DATE_INTERVAL
        ]);
        $attribute2 = create('ProductAttribute', 1, [
            'type_id'   => ProductAttributeType::TYPE_TEXT
        ]);

        $data = [
            'name'              => 'Termek 1',
            'description'       => 'asdf',
            'prices'            => [
                0   => 100
            ],
            'currencies'        => [
                0   => Currency::where('code', 'FT')->first()->id
            ],
            'attribute_ids'     => [$attribute1->id, $attribute2->id],
            'attribute_values'  => ['2019-01-05', '2019-01-15', 'text']
        ];

        $response = $this->post('/products', $data);
        $response->assertSessionHasNoErrors();

        $this->assertDatabaseHas('products', [
            'name'  => 'Termek 1'
        ]);

        $product = Product::where('name', 'Termek 1')->first();
        $this->assertCount(2, $product->attributes);

        $prodAttr1 = $product->attributes()->where('product_attribute_id', $attribute1->id)->first();
        $this->assertEquals([
            '2019-01-05', '2019-01-15'
        ], $prodAttr1->pivot->value);

        $prodAttr2 = $product->attributes()->where('product_attribute_id', $attribute2->id)->first();
        $this->assertEquals('text', $prodAttr2->pivot->value);
    }

    /** @test */
    public function create_with_only_multiple_attributes()
    {
        $this->signIn();

        $attribute1 = create('ProductAttribute', 1, [
            'type_id'   => ProductAttributeType::TYPE_DATE_INTERVAL
        ]);
        $attribute2 = create('ProductAttribute', 1, [
            'type_id'   => ProductAttributeType::TYPE_DATE_INTERVAL
        ]);
        $attribute3 = create('ProductAttribute', 1, [
            'type_id'   => ProductAttributeType::TYPE_DATE_INTERVAL
        ]);

        $data = [
            'name'              => 'Termek 1',
            'description'       => 'asdf',
            'prices'            => [
                0   => 100
            ],
            'currencies'        => [
                0   => Currency::where('code', 'FT')->first()->id
            ],
            'attribute_ids'     => [$attribute1->id, $attribute2->id, $attribute3->id],
            'attribute_values'  => ['2018-01-05', '2018-01-15', '2019-01-05', '2019-01-15', '2020-01-05', '2020-01-15']
        ];

        $response = $this->post('/products', $data);
        $response->assertSessionHasNoErrors();

        $this->assertDatabaseHas('products', [
            'name'  => 'Termek 1'
        ]);

        $product = Product::where('name', 'Termek 1')->first();
        $this->assertCount(3, $product->attributes);

        $prodAttr1 = $product->attributes()->where('product_attribute_id', $attribute1->id)->first();
        $this->assertEquals([
            '2018-01-05', '2018-01-15'
        ], $prodAttr1->pivot->value);

        $prodAttr2 = $product->attributes()->where('product_attribute_id', $attribute2->id)->first();
        $this->assertEquals([
            '2019-01-05', '2019-01-15'
        ], $prodAttr2->pivot->value);

        $prodAttr3 = $product->attributes()->where('product_attribute_id', $attribute3->id)->first();
        $this->assertEquals([
            '2020-01-05', '2020-01-15'
        ], $prodAttr3->pivot->value, true);
    }

    /** @test */
    public function regression_it_does_not_throw_error_because_of_product_photo_can_update_attribute()
    {
        // Régebben a view-ban volt egy user->can('update', $product) hívás, ami create oldalon hibát eredményezett, mivel
        // nincs $product object így nincs user->id sem, ami a policy -nél hibát eredményezett
        Cache::shouldReceive('get')
            ->times(1)
            ->with('currencies')
            ->andReturn(Currency::all());

        Cache::shouldReceive('get')
            ->times(1)
            ->with('attributeTypes')
            ->andReturn(ProductAttributeType::all());

        $response = $this->get('/products/create');
        $response->assertStatus(200);
        $response->assertSeeText('Termék létrehozása');
    }

    /** @test */
    public function it_returns_discount_attribute_as_string()
    {
        $product = create('Product');

        $discount = ProductAttribute::where('slug', ProductAttribute::DISCOUNT_SLUG)->first();

        $attr = new ProductProductAttribute;
        $attr->product_id = $product->id;
        $attr->product_attribute_id = $discount->id;
        $attr->value = 'ingyenes';
        $attr->save();

        $this->assertEquals('ingyenes', $product->discount);
    }

    /** @test */
    public function it_returns_null_as_discount_if_product_has_no_attribute()
    {
        $product = create('Product');

        $this->assertNull($product->discount);
    }
}
