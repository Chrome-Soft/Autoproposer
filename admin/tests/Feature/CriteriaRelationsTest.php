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
use App\UserData;
use Illuminate\Foundation\Testing\DatabaseMigrations;
use Illuminate\Foundation\Testing\WithoutMiddleware;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Foundation\Testing\RefreshDatabase;

class CriteriaRelationsTest extends TestCase
{
    use DatabaseMigrations;

    protected function setUp(): void
    {
        parent::setUp();
        $this->withExceptionHandling();

        $this->artisan('db:seed', ['--class' => 'CriteriaTableSeeder']);
        $this->artisan('db:seed', ['--class' => 'RelationTableSeeder']);
    }

    protected function setUpCache()
    {
        Cache::shouldReceive('get')
            ->times(1)
            ->with('relations')
            ->andReturn(Relation::all());
    }

    /** @test */
    public function it_returns_relations_to_page_visit_type()
    {
        $this->setUpCache();

        $criteria = Criteria::where('slug', 'visited_url')->first();
        $relations = $criteria->availableRelations;

        $ids = $relations->pluck('id');

        $this->assertContains(Relation::EQUAL, $ids);
        $this->assertContains(Relation::NOT_EQUAL, $ids);
    }

    /** @test */
    public function it_returns_relations_to_bool_type()
    {
        $this->setUpCache();

        $criteria = Criteria::where('slug', 'device_is_mobile')->first();
        $relations = $criteria->availableRelations;

        $ids = $relations->pluck('id');

        $this->assertContains(Relation::EQUAL, $ids);
        $this->assertContains(Relation::NOT_EQUAL, $ids);
    }

    /** @test */
    public function it_returns_relations_to_text_type()
    {
        $this->setUpCache();

        $criteria = Criteria::where('slug', 'device_manufacturer')->first();
        $relations = $criteria->availableRelations;

        $ids = $relations->pluck('id');

        $this->assertContains(Relation::EQUAL, $ids);
        $this->assertContains(Relation::NOT_EQUAL, $ids);
        $this->assertContains(Relation::CONTAIN, $ids);
        $this->assertContains(Relation::NOT_CONTAIN, $ids);
        $this->assertContains(Relation::EMPTY, $ids);
        $this->assertContains(Relation::NOT_EMPTY, $ids);
    }

    /** @test */
    public function it_returns_relations_to_number_type()
    {
        $this->setUpCache();

        $criteria = Criteria::where('slug', 'device_memory')->first();
        $relations = $criteria->availableRelations;

        $ids = $relations->pluck('id');

        $this->assertContains(Relation::EQUAL, $ids);
        $this->assertContains(Relation::NOT_EQUAL, $ids);
        $this->assertContains(Relation::EMPTY, $ids);
        $this->assertContains(Relation::NOT_EMPTY, $ids);
        $this->assertContains(Relation::GREATER_THEN_OR_EQUAL, $ids);
        $this->assertContains(Relation::LESS_THEN_OR_EQUAL, $ids);
    }

    /** @test */
    public function it_returns_a_whole_relation_map()
    {
        Cache::shouldReceive('get')
            ->times(1)
            ->with('criterias')
            ->andReturn(Criteria::all());

        Cache::shouldReceive('get')
            ->times(Criteria::all()->count())
            ->with('relations')
            ->andReturn(Relation::all());

        $map = Criteria::getAvailableRelationMap();

        $this->assertEquals(Criteria::all()->pluck('id')->toArray(), array_keys($map));
    }
}
