<?php

namespace Tests\Feature;

use App\Partner;
use App\Product;
use App\ProductAttribute;
use App\Proposer;
use App\Relation;
use Illuminate\Foundation\Testing\DatabaseMigrations;
use Illuminate\Support\Facades\Cache;

class ListableTest extends TestCase
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

    /** @test */
    public function it_lists_items()
    {
        create('Product', 2);

        $data = (new Product())->getListData($this->defaultPaging, []);

        $this->assertEquals(2, $data['count']);
    }

    /** @test */
    public function it_pages_items()
    {
        create('Product', 20);

        $data = (new Product())->getListData($this->defaultPaging, []);

        $this->assertEquals(20, $data['count']);
        $this->assertCount(10, $data['items']);
    }

    /** @test */
    public function it_applies_default_casters()
    {
        create('Product', 1, [
            'description'   => 'khjasfdhjklfdashjklfadshjkafsdjkhfajkhafdshjklfshjklfsjkfskhjfakhjlfadshjklfsadhjklafsd'
        ]);

        $data = (new Product())->getListData($this->defaultPaging, []);

        $this->assertTrue(strlen($data['items'][0]->description) < 25);
    }

    /** @test */
    public function it_applies_custom_casters()
    {
        $imported = create('ProductAttribute', 1, [
            'is_imported'   => 1
        ]);
        $nonImported = create('ProductAttribute', 1, [
            'is_imported'   => 0
        ]);

        $data = (new ProductAttribute())->getListData($this->defaultPaging, []);

        $importedItem = $data['items']->first(function ($x) use ($imported) { return $x->id == $imported->id; });
        $nonImportedItem = $data['items']->first(function ($x) use ($nonImported) { return $x->id == $nonImported->id; });

        $this->assertEquals('Igen', $importedItem->is_imported);
        $this->assertEquals('Nem', $nonImportedItem->is_imported);
    }

    /** @test */
    public function it_loads_relations()
    {
        $partner = create('Partner', 1, [
            'name'  => 'Partner 1'
        ]);
        create('Proposer', 1, [
            'partner_id'    => $partner->id
        ]);

        $data = (new Proposer())->getListData($this->defaultPaging, []);

        $this->assertEquals('Partner 1', $data['items'][0]->partner_id);
    }

    /** @test */
    public function it_orders_columns()
    {
        create('Product');

        $data = (new Product())->getListData($this->defaultPaging, []);

        $columns = array_keys($data['columns']);
        $this->assertEquals('name', $columns[0]);
        $this->assertEquals('description', $columns[1]);
        $this->assertEquals('created_at', $columns[count($columns) - 1]);
    }

    /** @test */
    public function it_returns_default_actions()
    {
        create('Product');

        $data = (new Product())->getListData($this->defaultPaging, []);

        $actions = array_keys($data['actions']);
        $this->assertContains('view', $actions);
        $this->assertContains('edit', $actions);
    }

    /** @test */
    public function it_applies_given_filter()
    {
        Cache::shouldReceive('get')
            ->times(1)
            ->with('relations')
            ->andReturn(Relation::all());

        create('Product', 1, ['name' => 'Product 1']);
        create('Product', 9);

        $filters = [
            ['column' => 'name', 'relation' => Relation::EQUAL, 'value' => 'Product 1']
        ];

        $data = (new Product())->getListData($this->defaultPaging, $filters);
        $this->assertEquals(1, $data['count']);
    }

    /** @test */
    public function it_applies_multiple_filters_with_and()
    {
        Cache::shouldReceive('get')
            ->times(2)
            ->with('relations')
            ->andReturn(Relation::all());

        $partnerFiltered1 = create('Partner', 1, ['name' => 'Partner 1', 'is_anonymus_domain' => 1, 'id' => 100]);
        $partnerFiltered2 = create('Partner', 1, ['name' => 'Partner 2', 'is_anonymus_domain' => 1, 'id' => 101]);
        create('Partner', 1, ['name' => 'Partner 3', 'is_anonymus_domain' => 0, 'id' => 102]);
        create('Product', 9);

        $filters = [
            ['column' => 'name', 'relation' => Relation::CONTAIN, 'value' => 'partner'],
            ['column' => 'is_anonymus_domain', 'relation' => Relation::EQUAL, 'value' => 'igen'],
        ];

        $data = (new Partner())->getListData($this->defaultPaging, $filters);
        $this->assertEquals(2, $data['count']);

        $this->assertTrue($data['items']->contains(function ($x) use ($partnerFiltered1) { return $x->id == $partnerFiltered1->id; }));
        $this->assertTrue($data['items']->contains(function ($x) use ($partnerFiltered2) { return $x->id == $partnerFiltered2->id; }));
    }

    /** @test */
    public function it_orders_items_by_default()
    {
        create('Product', 1, ['name' => 'X']);
        create('Product', 1, ['name' => 'Y']);
        create('Product', 1, ['name' => 'A']);
        create('Product', 1, ['name' => 'C']);

        $data = (new Product())->getListData($this->defaultPaging, []);

        $names = $data['items']->map(function ($x) { return $x->name; })->toArray();

        $this->assertEquals(['A', 'C', 'X', 'Y'], $names);
    }

    /** @test */
    public function it_excludes_unwanted_columns()
    {
        create('Product');

        $data = (new Product())->getListData($this->defaultPaging, []);

        $columns = array_keys($data['columns']);

        $this->assertNotContains('updated_at', $columns);
        $this->assertNotContains('user_id', $columns);

        $this->assertContains('name', $columns);
    }

    /** @test */
    public function it_contains_hidden_columns()
    {
        create('Product');

        $data = (new Product())->getListData($this->defaultPaging, []);

        $columns = array_keys($data['columns']);

        $this->assertContains('id', $columns);
        $this->assertContains('id', $data['hiddenColumns']);
    }
}
