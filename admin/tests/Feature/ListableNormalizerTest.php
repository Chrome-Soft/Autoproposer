<?php

namespace Tests\Feature;

use App\Partner;
use App\Product;
use App\ProductAttribute;
use App\Proposer;
use App\Relation;
use App\Services\Segment\ExpressionNormalizer\VersionNormalizer;
use App\UserData;
use Illuminate\Foundation\Testing\DatabaseMigrations;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;

class ListableNormalizerTest extends TestCase
{
    use DatabaseMigrations;

    private $defaultPaging = [
        'itemsPerPage' => 10,
        'currentPage' => 1,
    ];

    protected function setUp(): void
    {
        parent::setUp();
        $this->withExceptionHandling();

        $this->artisan('db:seed', ['--class' => 'ProductAttributeTableSeeder']);
        $this->artisan('db:seed', ['--class' => 'RelationTableSeeder']);
    }

    protected function setUpCache($n = 1)
    {
        Cache::shouldReceive('get')
            ->times($n)
            ->with('relations')
            ->andReturn(Relation::all());
    }

    /** @test */
    public function it_normalizes_bool_types()
    {
        $this->setUpCache();

        $anonymPartner = create('Partner', 1, ['is_anonymus_domain' => 1]);
        create('Partner', 1, ['is_anonymus_domain' => 0]);

        $filters = [
            ['column' => 'is_anonymus_domain', 'relation' => Relation::EQUAL, 'value' => 'Igen']
        ];

        $data = (new Partner)->getListData($this->defaultPaging, $filters);

        $this->assertEquals(1, $data['count']);
        $this->assertEquals($anonymPartner->id, $data['items']->first()->id);
    }

    /** @test */
    public function it_normalizes_contains_relation()
    {
        $this->setUpCache();

        $filteredPartner = create('Partner', 1, ['name' => 'Partner 1']);
        create('Partner');

        $filters = [
            ['column' => 'name', 'relation' => Relation::CONTAIN, 'value' => 'partner']
        ];

        $data = (new Partner)->getListData($this->defaultPaging, $filters);

        $this->assertEquals(1, $data['count']);
        $this->assertEquals($filteredPartner->id, $data['items']->first()->id);
    }

    /** @test */
    public function it_normalizes_not_contains_relation()
    {
        $this->setUpCache();

        create('Partner', 1, ['name' => 'Partner 1', 'id' => 100]);
        $filteredPartner = create('Partner', 1, ['id' => 101]);

        $filters = [
            ['column' => 'name', 'relation' => Relation::NOT_CONTAIN, 'value' => 'partner']
        ];

        $data = (new Partner)->getListData($this->defaultPaging, $filters);

        $this->assertEquals(1, $data['count']);
        $this->assertEquals($filteredPartner->id, $data['items']->first()->id);
    }

    /** @test */
    public function it_normalizes_empty_relation()
    {
        $this->setUpCache();

        $filteredProduct = create('Product', 1, ['description' => null]);
        create('Product', 1, ['description' => 'asdfasdfasdfsdafa']);

        $filters = [
            ['column' => 'description', 'relation' => Relation::EMPTY]
        ];

        $data = (new Product)->getListData($this->defaultPaging, $filters);

        $this->assertEquals(1, $data['count']);
        $this->assertEquals($filteredProduct->id, $data['items']->first()->id);
    }

    /** @test */
    public function it_normalizes_not_empty_relation()
    {
        $this->setUpCache();

        create('Product', 1, ['description' => null]);
        $filteredProduct = create('Product', 1, ['description' => 'asdfasdfasdfsdafa']);

        $filters = [
            ['column' => 'description', 'relation' => Relation::NOT_EMPTY]
        ];

        $data = (new Product)->getListData($this->defaultPaging, $filters);

        $this->assertEquals(1, $data['count']);
        $this->assertEquals($filteredProduct->id, $data['items']->first()->id);
    }

    /** @test */
    public function it_normalizes_version_column()
    {
        // Ehhez is mysql kapcsolat kéne teszt során, sqlite -ban nincs CONVERT és SUBSTRIN_INDEX
        $this->markTestSkipped();

        $filteredData1 = create('UserData', 1, ['os_version' => '12.0']);
        $filteredData2 = create('UserData', 1, ['os_version' => '11.3.7']);
        $filteredData3 = create('UserData', 1, ['os_version' => '11.2.4']);
        create('UserData', 1, ['os_version' => '11.0.8']);
        create('UserData', 1, ['os_version' => '10.9']);

        $filters = [
            ['column' => 'os_version', 'relation' => Relation::GREATER_THEN_OR_EQUAL, 'value' => '11.1.2']
        ];

        $normalizer = new VersionNormalizer('os_version', Relation::GREATER_THEN_OR_EQUAL, '11.1.2');

        $sql = "SELECT * FROM user_data WHERE " . $normalizer->normalize();
        $items = DB::select($sql);

        $this->assertEquals(3, count($items));
    }

    /** @test */
    public function it_normalizes_created_at_with_date_and_time()
    {
        $this->setUpCache();

        $filteredProduct = create('Product', 1, ['created_at' => '2019-04-01 16:32:00']);
        create('Product', 1, ['created_at' => '2019-04-01 15:22:00']);

        $filters = [
            ['column' => 'created_at', 'relation' => Relation::GREATER_THEN_OR_EQUAL, 'value' => '2019-04-01 15:33:00']
        ];

        $data = (new Product)->getListData($this->defaultPaging, $filters);

        $this->assertEquals(1, $data['count']);
        $this->assertEquals($filteredProduct->id, $data['items']->first()->id);
    }

    /** @test */
    public function it_normalizes_created_at_only_with_date()
    {
        $this->setUpCache();

        $filteredProduct1 = create('Product', 1, ['created_at' => '2019-04-01 16:32:00']);
        $filteredProduct2 = create('Product', 1, ['created_at' => '2019-04-01 15:22:00']);
        create('Product', 1, ['created_at' => '2019-03-30 15:22:00']);

        $filters = [
            ['column' => 'created_at', 'relation' => Relation::GREATER_THEN_OR_EQUAL, 'value' => '2019-04-01']
        ];

        $data = (new Product)->getListData($this->defaultPaging, $filters);

        $this->assertEquals(2, $data['count']);
    }
}
