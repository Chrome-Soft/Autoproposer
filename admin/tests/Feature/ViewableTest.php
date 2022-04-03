<?php

namespace Tests\Feature;

use App\Partner;
use App\Product;
use App\ProductAttribute;
use App\Proposer;
use App\ProposerType;
use App\Relation;
use Illuminate\Foundation\Testing\DatabaseMigrations;
use Illuminate\Support\Facades\Cache;

class ViewableTest extends TestCase
{
    use DatabaseMigrations;

    protected function setUp(): void
    {
        parent::setUp();
        $this->withExceptionHandling();

        $this->artisan('db:seed', ['--class' => 'ProductAttributeTableSeeder']);
        $this->artisan('db:seed', ['--class' => 'RelationTableSeeder']);
    }

    /** @test */
    public function it_loads_values_with_labels()
    {
        $product = create('Product', 1, ['name' => 'Product #1']);

        $data = $product->getViewData();

        $this->assertEquals('Név', $data['data']['name']['label']);
        $this->assertEquals('Product #1', $data['data']['name']['value']);
    }

    /** @test */
    public function it_loads_field_in_order()
    {
        $product = create('Product');

        $data = $product->getViewData();

        $this->assertEquals('name', $data['fields'][0]);
        $this->assertEquals('created_at', $data['fields'][count($data['fields']) - 1]);
    }

    /** @test */
    public function it_applies_casters()
    {
        Cache::shouldReceive('get')
            ->with('proposer_types')
            ->andReturn(ProposerType::all());

        $proposer = create('Proposer', 1, ['type_id' => 1, 'width' => 50, 'height' => 250]);

        $data = $proposer->getViewData();

        $this->assertEquals('50px', $data['data']['width']['value']);
        $this->assertEquals('250px', $data['data']['height']['value']);
    }

    /** @test */
    public function it_loads_relations()
    {
        Cache::shouldReceive('get')
            ->with('proposer_types')
            ->andReturn(ProposerType::all());

        $partner = create('Partner', 1, ['name' => 'Partner #1']);
        $proposer = create('Proposer', 1, ['partner_id' => $partner->id]);

        $data = $proposer->getViewData();

        $this->assertEquals('Partner #1', $data['data']['partner_id']['value']);
    }
}
