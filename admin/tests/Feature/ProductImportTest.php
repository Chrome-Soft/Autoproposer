<?php

namespace Tests\Feature;

use App\Currency;
use App\PageLoad;
use App\Product;
use App\ProductAttribute;
use App\ProductAttributeType;
use App\ProductProductAttribute;
use App\Services\HttpClient;
use App\Services\ProductImport\ImportOptions;
use App\Services\ProductImport\ProductBatchCreate;
use App\Services\ProductImport\ProductImportService;
use App\Services\ProductImport\ProductImportStatistics;
use App\UserData;
use Illuminate\Foundation\Testing\DatabaseMigrations;
use Illuminate\Foundation\Testing\WithoutMiddleware;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\App;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Foundation\Testing\RefreshDatabase;

class ProductImportTest extends TestCase
{
    use DatabaseMigrations;

    private $options;

    protected function setUp(): void
    {
        parent::setUp();
        $this->withExceptionHandling();

        $this->artisan('db:seed', ['--class' => 'CurrencyTableSeeder']);
        $this->artisan('db:seed', ['--class' => 'ProductAttributeTableSeeder']);

        $this->options = new ImportOptions('http://base.url', '/endpoint', 'api-key-hash', '/storage/hash/hash.json');
    }

    private function mockCache($n = 1)
    {
        Cache::shouldReceive('get')
            ->times($n)
            ->with('currencies')
            ->andReturn(Currency::all());
    }

    private function createClientMock($data)
    {
        $clientMock = $this->createMock(HttpClient::class);

        $callback = function ($x) use ($data) {
            $switcher = ['/endpoint' => $data, '/storage/hash/hash.json' => ['api-key']];
            return $switcher[$x];
        };

        $clientMock
            ->expects($this->exactly(2))
            ->method('get')
            ->with($this->logicalOr(
                $this->equalTo('/endpoint'),
                $this->equalTo('/storage/hash/hash.json')
            ))
            ->will($this->returnCallback($callback));

        return $clientMock;
    }

    /** @test */
    public function import_card()
    {
        $this->mockCache();
        $this->signIn();

        $data = $this->createCard();
        $clientMock = $this->createClientMock($data);

        $importService = new ProductImportService($clientMock, new ProductImportStatistics, $this->options, new ProductBatchCreate);
        $stat = $importService->import();

        $this->assertEquals(1, $stat->passed);

        $product = Product::first();
        $this->assertEquals($data[0]['name'], $product->name);

        $this->assertCount(1, $product->photos);
        $this->assertCount(2, $product->prices);
        $this->assertCount(3, $product->attributes);

        // Érvényességi intervallum
        $validityIntervall = ProductAttribute::where('slug', 'ervenyessegi-intervallum')->first();
        $validityAttr = $product->attributes()->where('product_attribute_id', $validityIntervall->id)->first();

        $this->assertEquals($data[0]['validity']['from'], $validityAttr->pivot->value[0]);
        $this->assertEquals($data[0]['validity']['to'], $validityAttr->pivot->value[1]);

        // Kedvezmények
        $discount20 = ProductAttribute::where('slug', '20-kedvezeny-felnott')->first();
        $discount20Attr = $product->attributes()->where('product_attribute_id', $discount20->id)->first();

        $this->assertContains('Sárvár', $discount20Attr->pivot->value);
        $this->assertContains('Celldömölk', $discount20Attr->pivot->value);
        $this->assertNotContains('Szombathely', $discount20Attr->pivot->value);

        $discount50 = ProductAttribute::where('slug', '50-kedvezeny-gyerek')->first();
        $discount50Attr = $product->attributes()->where('product_attribute_id', $discount50->id)->first();

        $this->assertContains('Szombathely', $discount50Attr->pivot->value);
        $this->assertNotContains('Celldömölk', $discount50Attr->pivot->value);
        $this->assertNotContains('Sárvár', $discount50Attr->pivot->value);
    }

    /** @test */
    public function import_ticket()
    {
        $this->mockCache();
        $this->signIn();

        $data = $this->createTicket();
        $clientMock = $this->createClientMock($data);

        $importService = new ProductImportService($clientMock, new ProductImportStatistics, $this->options, new ProductBatchCreate);
        $stat = $importService->import();

        $this->assertEquals(1, $stat->passed);

        $product = Product::first();
        $this->assertEquals($data[0]['name'], $product->name);

        $this->assertCount(1, $product->photos);
        $this->assertCount(2, $product->prices);
        $this->assertCount(4, $product->attributes);

        // Érvényességi intervallum - whenToSwitch
        $validityIntervall = ProductAttribute::where('slug', 'ervenyessegi-intervallum')->first();
        $validityAttr = $product->attributes()->where('product_attribute_id', $validityIntervall->id)->first();

        $this->assertEquals($data[0]['whenToSwitch']['from'], $validityAttr->pivot->value[0]);
        $this->assertEquals($data[0]['whenToSwitch']['to'], $validityAttr->pivot->value[1]);

        // Vásárlási intervallum - whenToBuy
        $buyIntervall = ProductAttribute::where('slug', 'vasarlasi-intervallum')->first();
        $buyAttr = $product->attributes()->where('product_attribute_id', $buyIntervall->id)->first();

        $this->assertEquals($data[0]['whenToBuy']['from'], $buyAttr->pivot->value[0]);
        $this->assertEquals($data[0]['whenToBuy']['to'], $buyAttr->pivot->value[1]);

        // Kedvezmények
        $discount20 = ProductAttribute::where('slug', '20-kedvezeny-felnott')->first();
        $discount20Attr = $product->attributes()->where('product_attribute_id', $discount20->id)->first();

        $this->assertContains('Sárvár', $discount20Attr->pivot->value);
        $this->assertContains('Celldömölk', $discount20Attr->pivot->value);
        $this->assertNotContains('Szombathely', $discount20Attr->pivot->value);

        $discount50 = ProductAttribute::where('slug', '50-kedvezeny-gyerek')->first();
        $discount50Attr = $product->attributes()->where('product_attribute_id', $discount50->id)->first();

        $this->assertContains('Szombathely', $discount50Attr->pivot->value);
        $this->assertNotContains('Celldömölk', $discount50Attr->pivot->value);
        $this->assertNotContains('Sárvár', $discount50Attr->pivot->value);
    }

    /** @test */
    public function import_multiple_items()
    {
        $this->mockCache(2);
        $this->signIn();

        $data = $this->createMultipleItems();
        $clientMock = $this->createClientMock($data);

        $importService = new ProductImportService($clientMock, new ProductImportStatistics, $this->options, new ProductBatchCreate);
        $stat = $importService->import();
        $this->assertEquals(2, $stat->passed);

        $products = Product::all();
        $card = $products->first();
        $ticket = $products->last();

        $this->assertCount(2, $products);
        $this->assertEquals($data[0]['name'], $card->name);
        $this->assertEquals($data[1]['name'], $ticket->name);

        $this->assertCount(1, $card->photos);
        $this->assertCount(2, $card->prices);
        $this->assertCount(3, $card->attributes);

        $this->assertCount(1, $ticket->photos);
        $this->assertCount(2, $ticket->prices);
        $this->assertCount(4, $ticket->attributes);
    }

    /** @test */
    public function import_multiple_items_with_errors()
    {
        $this->mockCache(2);
        $this->signIn();

        $data = $this->createMultipleItems();
        $data[0]['pictures'][0]['fullSize'] = null;
        $data[0]['prices'][0]['currency'] = 'not-valid-currency';
        $data[1]['whenToBuy'] = null;
        $data[1]['whenToSwitch'] = null;

        $clientMock = $this->createClientMock($data);

        $importService = new ProductImportService($clientMock, new ProductImportStatistics, $this->options, new ProductBatchCreate);
        $stat = $importService->import();
        $this->assertEquals(0, $stat->passed);
        $this->assertEquals(2, $stat->createdWithError);

        $products = Product::all();
        $cardWithError = $products->first();
        $ticketWithError = $products->last();

        $this->assertCount(2, $products);

        $this->assertEquals($data[0]['name'], $cardWithError->name);
        $this->assertCount(0, $cardWithError->photos);
        $this->assertCount(0, $cardWithError->prices);
        $this->assertCount(3, $cardWithError->attributes);

        $this->assertEquals($data[1]['name'], $ticketWithError->name);
        $this->assertCount(2, $ticketWithError->prices);
        $this->assertCount(1, $ticketWithError->photos);
        $this->assertCount(2, $ticketWithError->attributes);
    }

    /** @test */
    public function import_failed_product()
    {
        $this->mockCache(0);
        $this->signIn();

        $data = $this->createCard();
        $data[0]['name'] = null;

        $clientMock = $this->createClientMock($data);

        $importService = new ProductImportService($clientMock, new ProductImportStatistics, $this->options, new ProductBatchCreate);
        $stat = $importService->import();
        $this->assertEquals(0, $stat->passed);
        $this->assertEquals(0, $stat->createdWithError);
        $this->assertEquals(1, $stat->failed);

        $products = Product::all();
        $this->assertEmpty($products);
    }

    /** @test */
    public function regression_is_saves_photo_with_http_prefix()
    {
        $this->mockCache(1);
        $this->signIn();

        $data = $this->createCard();
        $data[0]['pictures'][0]['fullSize'] = '';

        $clientMock = $this->createClientMock($data);

        $importService = new ProductImportService($clientMock, new ProductImportStatistics, $this->options, new ProductBatchCreate);
        $stat = $importService->import();
        $this->assertEquals(1, $stat->passed);
        $this->assertEquals(0, $stat->createdWithError);
        $this->assertEquals(0, $stat->failed);

        $products = Product::all();
        $product = $products->first();

        $this->assertCount(1, $product->photos);
        $this->assertEquals('', $product->photos->first()->image_path);
    }

    protected function createCard()
    {
        return [
            array_merge($this->baseData(),
            [
                'name' => 'Kirakodó vásár 2019-04-17 08:04  ',
                'type' => 'card_types',
                'validity' => [
                    'from' => '2019-04-16 12:00',
                    'to' => '2019-05-16 12:00'
                ]
            ])
        ];
    }

    protected function createTicket()
    {
        return [
            array_merge($this->baseData(),
            [
                'type' => 'tickets',
                'name' => 'Kirakodó vásár 2019-04-17 08:04 ',
                'whenToBuy' => [
                    'from' => '2019-04-16 12:00',
                    'to' => '2019-05-16 12:00'
                ],
                'whenToSwitch' => [
                    'from' => '2019-04-16 12:00',
                    'to' => '2019-05-16 12:00'
                ],
            ])
        ];
    }

    protected function createMultipleItems()
    {
        return [
            $this->createCard()[0],
            $this->createTicket()[0]
        ];
    }

    protected function baseData()
    {
        return [
            'description' => 'Leírás',
            'pictures' => [
                [
                    'isDefault' => true,
                    'fullSize' => '',
                    'thumb' => ''
                ],
                [
                    'fullSize' => '',
                    'thumb' => ''
                ]
            ],
            'prices' => [
                [
                    'price' => 127,
                    'currency' => 'Ft'
                ],
                [
                    'price' => 1,
                    'currency' => '€'
                ]
            ],
            'availableQuantity' => 496,
            'partner' => [
                'name' => '',
                'address' => ''
            ],
            'discounts' => [
                [
                    // minden kedvezményhez egy partner van. Ha ugyanaz a kedv több partnerre is érvényes, akkor többször szerepel ugyanaz a name csak más partnerrel
                    'name' => '20% kedvezény felnőtt',
                    'value' => '20%',
                    'partner' => [
                        'name' => '',
                        'address' => ''
                    ]
                ],
                [
                    // minden kedvezményhez egy partner van. Ha ugyanaz a kedv több partnerre is érvényes, akkor többször szerepel ugyanaz a name csak más partnerrel
                    'name' => '',
                    'value' => '',
                    'partner' => [
                        'name' => '',
                        'address' => ''
                    ]
                ],
                [
                    // minden kedvezményhez egy partner van. Ha ugyanaz a kedv több partnerre is érvényes, akkor többször szerepel ugyanaz a name csak más partnerrel
                    'name' => '50% kedvezény gyerek',
                    'value' => '50%',
                    'partner' => [
                        'name' => '',
                        'address' => ''
                    ]
                ]
            ]
        ];
    }

    /** @test */
    public function regression_it_shows_501_page_if_no_config_set()
    {
        config(['url' => null]);

        $response = $this->get('products/import');
        $response->assertStatus(501);
        $response->assertSeeText('Ez a szolgáltatás jelenleg nem elérhető');
    }
}
