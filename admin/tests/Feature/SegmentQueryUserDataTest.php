<?php

namespace Tests\Feature;

use App\Criteria;
use App\Relation;
use App\SegmentGroup;
use App\SegmentGroupCriteria;
use App\UserData;
use Illuminate\Foundation\Testing\DatabaseMigrations;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Config;

class SegmentQueryUserDataTest extends TestCase
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
    public function two_groups_with_and()
    {
        $this->signIn();

        $segment = create('Segment', 1, [
            'name'  => 'Active segment 1'
        ]);

        // Megfelelnek a feltételnek
        $userDataPass1 = create('UserData', 1, [
            'device_manufacturer'   => 'samsung',
            'device_is_mobile'      => 1
        ]);
        $userDataPass2 = create('UserData', 1, [
            'device_manufacturer'   => 'apple',
            'device_is_mobile'      => 1
        ]);

        // Nem felelnek meg a feltételeknek
        $userDataNotPass1 = create('UserData', 1, [
            'device_manufacturer'   => 'huawei',
            'device_is_mobile'      => 1
        ]);
        $userDataNotPass2 = create('UserData', 1, [
            'device_manufacturer'   => 'apple',
            'device_is_mobile'      => 0
        ]);

        // (gyártó = samsung VAGY gyártó = apple) ÉS (mobil)

        // ---- GROUP 1
        $group1 = new SegmentGroup;
        $group1->segment_id = $segment->id;
        $group1->bool_type = 'and';
        $group1->save();

        $c1 = new SegmentGroupCriteria;
        $c1->segment_group_id = $group1->id;
        $c1->criteria_id = Criteria::where('slug', 'device_manufacturer')->first()->id;
        $c1->relation_id = Relation::where('symbol', '=')->first()->id;
        $c1->value = 'samsung';
        $c1->bool_type = 'or';
        $c1->save();

        $c2 = new SegmentGroupCriteria;
        $c2->segment_group_id = $group1->id;
        $c2->criteria_id = Criteria::where('slug', 'device_manufacturer')->first()->id;
        $c2->relation_id = Relation::where('symbol', '=')->first()->id;
        $c2->value = 'apple';

        $c2->save();

        // ---- GROUP 2
        $group2 = new SegmentGroup;
        $group2->segment_id = $segment->id;
        $group2->save();

        $c3 = new SegmentGroupCriteria;
        $c3->segment_group_id = $group2->id;
        $c3->criteria_id = Criteria::where('slug', 'device_is_mobile')->first()->id;
        $c3->relation_id = Relation::where('symbol', '=')->first()->id;
        $c3->value = 'igen';
        $c3->save();

        $query = $segment->buildQuery();
        $items = $query->get();
        $ids = $items->map(function ($x) { return $x->id;});

        $this->assertCount(2, $items);

        $this->assertContains($userDataPass1->id, $ids);
        $this->assertContains($userDataPass2->id, $ids);

        $this->assertNotContains($userDataNotPass1->id, $ids);
        $this->assertNotContains($userDataNotPass2->id, $ids);
    }

    /** @test */
    public function two_groups_with_or()
    {
        $this->signIn();

        $segment = create('Segment', 1, [
            'name'  => 'Active segment 1'
        ]);

        // Megfelelnek a feltételnek
        $userDataPass1 = create('UserData', 1, [
            'device_memory'         => 4,
            'device_screen_width'   => 500,
            'device_is_mobile'      => 0
        ]);
        $userDataPass2 = create('UserData', 1, [
            'device_memory'         => 2,
            'device_screen_width'   => 300,
            'device_is_mobile'      => 1
        ]);

        // Nem felelnek meg a feltételeknek
        $userDataNotPass = create('UserData', 1, [
            'device_memory'         => 2,
            'device_screen_width'   => 300,
            'device_is_mobile'      => 0
        ]);

        // (memória >= 4gb ÉS felbontás szélesség >= 500) VAGY (mobil)

        // ---- GROUP 1
        $group1 = new SegmentGroup;
        $group1->segment_id = $segment->id;
        $group1->bool_type = 'or';
        $group1->save();

        $c1 = new SegmentGroupCriteria;
        $c1->segment_group_id = $group1->id;
        $c1->criteria_id = Criteria::where('slug', 'device_memory')->first()->id;
        $c1->relation_id = Relation::where('symbol', '>=')->first()->id;
        $c1->value = 4;
        $c1->bool_type = 'and';
        $c1->save();

        $c2 = new SegmentGroupCriteria;
        $c2->segment_group_id = $group1->id;
        $c2->criteria_id = Criteria::where('slug', 'device_screen_width')->first()->id;
        $c2->relation_id = Relation::where('symbol', '>=')->first()->id;
        $c2->value = 500;

        $c2->save();

        // ---- GROUP 2
        $group2 = new SegmentGroup;
        $group2->segment_id = $segment->id;
        $group2->save();

        $c3 = new SegmentGroupCriteria;
        $c3->segment_group_id = $group2->id;
        $c3->criteria_id = Criteria::where('slug', 'device_is_mobile')->first()->id;
        $c3->relation_id = Relation::where('symbol', '=')->first()->id;
        $c3->value = 'igen';
        $c3->save();

        $query = $segment->buildQuery();
        $items = $query->get();
        $ids = $items->map(function ($x) { return $x->id;});

        $this->assertCount(2, $items);

        $this->assertContains($userDataPass1->id, $ids);
        $this->assertContains($userDataPass2->id, $ids);

        $this->assertNotContains($userDataNotPass->id, $ids);
    }
    
    /** @test */
    public function it_applies_nullable_normalizer_as_is_null()
    {
        $this->signIn();

        $segment = create('Segment', 1, [
            'name'  => 'Active segment 1'
        ]);

        // Megfelel a feltételnek
        $userDataPass = create('UserData', 1, [
            'device_memory'      => null
        ]);

        // Nem felel meg a feltételeknek
        $userDataNotPass = create('UserData', 1, [
            'device_memory'      => 8
        ]);

        // ---- GROUP 1
        $group1 = new SegmentGroup;
        $group1->segment_id = $segment->id;
        $group1->save();

        $c1 = new SegmentGroupCriteria;
        $c1->segment_group_id = $group1->id;
        $c1->criteria_id = Criteria::where('slug', 'device_memory')->first()->id;
        $c1->relation_id = Relation::where('symbol', 'IS NULL')->first()->id;
        $c1->value = '';
        $c1->save();

        $query = $segment->buildQuery();
        $items = $query->get();
        $ids = $items->map(function ($x) { return $x->id;});

        $this->assertCount(1, $items);

        $this->assertContains($userDataPass->id, $ids);
        $this->assertNotContains($userDataNotPass->id, $ids);
    }

    /** @test */
    public function it_applies_nullable_normalizer_as_is_not_null()
    {
        $this->signIn();

        $segment = create('Segment', 1, [
            'name'  => 'Active segment 1'
        ]);

        // Megfelel a feltételnek
        $userDataPass = create('UserData', 1, [
            'device_memory'      => 8
        ]);

        // Nem felel meg a feltételeknek
        $userDataNotPass = create('UserData', 1, [
            'device_memory'      => null
        ]);

        // ---- GROUP 1
        $group1 = new SegmentGroup;
        $group1->segment_id = $segment->id;
        $group1->save();

        $c1 = new SegmentGroupCriteria;
        $c1->segment_group_id = $group1->id;
        $c1->criteria_id = Criteria::where('slug', 'device_memory')->first()->id;
        $c1->relation_id = Relation::where('symbol', 'IS NOT NULL')->first()->id;
        $c1->value = '';
        $c1->save();

        $query = $segment->buildQuery();
        $items = $query->get();
        $ids = $items->map(function ($x) { return $x->id;});

        $this->assertCount(1, $items);

        $this->assertContains($userDataPass->id, $ids);
        $this->assertNotContains($userDataNotPass->id, $ids);
    }

    /** @test */
    public function it_applies_visited_url_normalizer_with_equal()
    {
        $this->signIn();

        $pageMain = create('Page', 1, ['url' => '/']);
        $pageProducts = create('Page', 1, ['url' => '/termekek']);
        $pageContact = create('Page', 1, ['url' => '/kapcsolat']);

        $segment = create('Segment', 1, [
            'name'  => 'Active segment 1'
        ]);

        // Megfelel a feltételnek
        $userDataPass1 = create('UserData', 1, [
            'device_manufacturer'       => 'apple',
            'cookie_id'                 => '#1'
        ]);
        $pageLoad1 = create('PageLoad', 1, [
            'from_url'  => '/termekek',
            'to_url'    => '/kapcsolat',
            'cookie_id' => '#1'
        ]);

        // Megfelel a feltételnek
        $userDataPass2 = create('UserData', 1, [
            'device_manufacturer'       => 'apple',
            'cookie_id'                 => '#2'
        ]);
        $pageLoad2 = create('PageLoad', 1, [
            'from_url'  => '/',
            'to_url'    => '/termekek',
            'cookie_id' => '#2'
        ]);

        // Nem felel meg a feltételeknek
        $userDataNotPass = create('UserData', 1, [
            'device_manufacturer'       => '',
            'cookie_id'                 => '#3'
        ]);
        $pageLoad3 = create('PageLoad', 1, [
            'from_url'  => '/',
            'to_url'    => '/rolunk',
            'cookie_id' => '#3'
        ]);

        // ---- GROUP 1
        $group1 = new SegmentGroup;
        $group1->segment_id = $segment->id;
        $group1->save();

        $c1 = new SegmentGroupCriteria;
        $c1->segment_group_id = $group1->id;
        $c1->criteria_id = Criteria::where('slug', 'visited_url')->first()->id;
        $c1->relation_id = Relation::where('symbol', '=')->first()->id;
        $c1->value = json_encode(['page_id' => $pageProducts->id]);
        $c1->save();

        $query = $segment->buildQuery();
        $items = $query->get();
        $ids = $items->map(function ($x) { return $x->id;});

        $this->assertCount(2, $items);

        $this->assertContains($userDataPass1->id, $ids);
        $this->assertContains($userDataPass2->id, $ids);
        $this->assertNotContains($userDataNotPass->id, $ids);
    }

    /** @test */
    public function it_applies_visited_url_normalizer_with_not_equal()
    {
        $this->signIn();

        $pageMain = create('Page', 1, ['url' => '/']);
        $pageProducts = create('Page', 1, ['url' => '/termekek']);
        $pageContact = create('Page', 1, ['url' => '/kapcsolat']);

        $segment = create('Segment', 1, [
            'name'  => 'Active segment 1'
        ]);

        // Megfelel a feltételnek
        $userDataNotPass1 = create('UserData', 1, [
            'device_manufacturer'       => 'apple',
            'cookie_id'                 => '#1'
        ]);
        $pageLoad1 = create('PageLoad', 1, [
            'from_url'  => '/termekek',
            'to_url'    => '/kapcsolat',
            'cookie_id' => '#1'
        ]);

        // Megfelel a feltételnek
        $userDataNotPass2 = create('UserData', 1, [
            'device_manufacturer'       => 'apple',
            'cookie_id'                 => '#2'
        ]);
        $pageLoad2 = create('PageLoad', 1, [
            'from_url'  => '/',
            'to_url'    => '/termekek',
            'cookie_id' => '#2'
        ]);

        // Nem felel meg a feltételeknek
        $userDataPass = create('UserData', 1, [
            'device_manufacturer'       => '',
            'cookie_id'                 => '#3'
        ]);
        $pageLoad3 = create('PageLoad', 1, [
            'from_url'  => '/',
            'to_url'    => '/rolunk',
            'cookie_id' => '#3'
        ]);

        // ---- GROUP 1
        $group1 = new SegmentGroup;
        $group1->segment_id = $segment->id;
        $group1->save();

        $c1 = new SegmentGroupCriteria;
        $c1->segment_group_id = $group1->id;
        $c1->criteria_id = Criteria::where('slug', 'visited_url')->first()->id;
        $c1->relation_id = Relation::where('symbol', '!=')->first()->id;
        $c1->value = json_encode(['page_id' => $pageProducts->id]);
        $c1->save();

        $query = $segment->buildQuery();
        $items = $query->get();
        $ids = $items->map(function ($x) { return $x->id;});

        $this->assertCount(1, $items);

        $this->assertContains($userDataPass->id, $ids);
        $this->assertNotContains($userDataNotPass1->id, $ids);
        $this->assertNotContains($userDataNotPass2->id, $ids);
    }

    /** @test */
    public function it_applies_visited_path_normalizer_with_equal()
    {
        $this->signIn();

        $pageMain = create('Page', 1, ['url' => '/']);
        $pageProducts = create('Page', 1, ['url' => '/termekek']);
        $pageContact = create('Page', 1, ['url' => '/kapcsolat']);

        $segment = create('Segment', 1, [
            'name'  => 'Active segment 1'
        ]);

        // Megfelel a feltételnek
        $userDataPass1 = create('UserData', 1, [
            'device_manufacturer'       => 'apple',
            'cookie_id'                 => '#1'
        ]);
        $pageLoad1 = create('PageLoad', 1, [
            'from_url'  => '/termekek',
            'to_url'    => '/kapcsolat',
            'cookie_id' => '#1'
        ]);

        // Megfelel a feltételnek
        $userDataNotPass1 = create('UserData', 1, [
            'device_manufacturer'       => 'apple',
            'cookie_id'                 => '#2'
        ]);
        $pageLoad2 = create('PageLoad', 1, [
            'from_url'  => '/',
            'to_url'    => '/termekek',
            'cookie_id' => '#2'
        ]);

        // Nem felel meg a feltételeknek
        $userDataNotPass2 = create('UserData', 1, [
            'device_manufacturer'       => '',
            'cookie_id'                 => '#3'
        ]);
        $pageLoad3 = create('PageLoad', 1, [
            'from_url'  => '/',
            'to_url'    => '/rolunk',
            'cookie_id' => '#3'
        ]);

        // ---- GROUP 1
        $group1 = new SegmentGroup;
        $group1->segment_id = $segment->id;
        $group1->save();

        $c1 = new SegmentGroupCriteria;
        $c1->segment_group_id = $group1->id;
        $c1->criteria_id = Criteria::where('slug', 'visited_path')->first()->id;
        $c1->relation_id = Relation::where('symbol', '=')->first()->id;
        $c1->value = json_encode(['from_page_id' => $pageProducts->id, 'to_page_id' => $pageContact->id]);
        $c1->save();

        $query = $segment->buildQuery();
        $items = $query->get();
        $ids = $items->map(function ($x) { return $x->id;});

        $this->assertCount(1, $items);

        $this->assertContains($userDataPass1->id, $ids);

        $this->assertNotContains($userDataNotPass1->id, $ids);
        $this->assertNotContains($userDataNotPass2->id, $ids);
    }

    /** @test */
    public function it_applies_visited_path_normalizer_with_not_equal()
    {
        $this->signIn();

        $pageMain = create('Page', 1, ['url' => '/']);
        $pageProducts = create('Page', 1, ['url' => '/termekek']);
        $pageContact = create('Page', 1, ['url' => '/kapcsolat']);

        $segment = create('Segment', 1, [
            'name'  => 'Active segment 1'
        ]);

        // Megfelel a feltételnek
        $userDataNotPass = create('UserData', 1, [
            'device_manufacturer'       => 'apple',
            'cookie_id'                 => '#1'
        ]);
        $pageLoad1 = create('PageLoad', 1, [
            'from_url'  => '/termekek',
            'to_url'    => '/kapcsolat',
            'cookie_id' => '#1'
        ]);

        // Megfelel a feltételnek
        $userDataPass1 = create('UserData', 1, [
            'device_manufacturer'       => 'apple',
            'cookie_id'                 => '#2'
        ]);
        $pageLoad2 = create('PageLoad', 1, [
            'from_url'  => '/',
            'to_url'    => '/termekek',
            'cookie_id' => '#2'
        ]);

        // Nem felel meg a feltételeknek
        $userDataPass2 = create('UserData', 1, [
            'device_manufacturer'       => '',
            'cookie_id'                 => '#3'
        ]);
        $pageLoad3 = create('PageLoad', 1, [
            'from_url'  => '/',
            'to_url'    => '/rolunk',
            'cookie_id' => '#3'
        ]);

        // ---- GROUP 1
        $group1 = new SegmentGroup;
        $group1->segment_id = $segment->id;
        $group1->save();

        $c1 = new SegmentGroupCriteria;
        $c1->segment_group_id = $group1->id;
        $c1->criteria_id = Criteria::where('slug', 'visited_path')->first()->id;
        $c1->relation_id = Relation::where('symbol', '!=')->first()->id;
        $c1->value = json_encode(['from_page_id' => $pageProducts->id, 'to_page_id' => $pageContact->id]);
        $c1->save();

        $query = $segment->buildQuery();
        $items = $query->get();
        $ids = $items->map(function ($x) { return $x->id;});

        $this->assertCount(2, $items);

        $this->assertContains($userDataPass1->id, $ids);
        $this->assertContains($userDataPass2->id, $ids);

        $this->assertNotContains($userDataNotPass->id, $ids);
    }

    /** @test */
    public function it_applies_contains_normalizer_with_like()
    {
        $this->signIn();

        $segment = create('Segment', 1, [
            'name'  => 'Active segment 1'
        ]);

        // Megfelelnek a feltételnek
        $userDataPass1 = create('UserData', 1, [
            'device_manufacturer'   => 'asdf123',
        ]);
        $userDataPass2 = create('UserData', 1, [
            'device_manufacturer'   => 'asdf234',
            'device_is_mobile'      => 1
        ]);

        // Nem felelnek meg a feltételeknek
        $userDataNotPass1 = create('UserData', 1, [
            'device_manufacturer'   => 'huawei',
            'device_is_mobile'      => 1
        ]);

        // ---- GROUP 1
        $group1 = new SegmentGroup;
        $group1->segment_id = $segment->id;
        $group1->save();

        $c1 = new SegmentGroupCriteria;
        $c1->segment_group_id = $group1->id;
        $c1->criteria_id = Criteria::where('slug', 'device_manufacturer')->first()->id;
        $c1->relation_id = Relation::where('symbol', 'LIKE')->first()->id;
        $c1->value = 'asdf';
        $c1->save();

        $query = $segment->buildQuery();
        $items = $query->get();
        $ids = $items->map(function ($x) { return $x->id;});

        $this->assertCount(2, $items);

        $this->assertContains($userDataPass1->id, $ids);
        $this->assertContains($userDataPass2->id, $ids);

        $this->assertNotContains($userDataNotPass1->id, $ids);
    }

    /** @test */
    public function it_applies_contains_normalizer_with_not_like()
    {
        $this->signIn();

        $segment = create('Segment', 1, [
            'name'  => 'Active segment 1'
        ]);

        // Megfelelnek a feltételnek
        $userDataNotPass1 = create('UserData', 1, [
            'device_manufacturer'   => 'asdf123',
        ]);
        $userDataNotPass2 = create('UserData', 1, [
            'device_manufacturer'   => 'asdf234',
            'device_is_mobile'      => 1
        ]);

        // Nem felelnek meg a feltételeknek
        $userDataPass1 = create('UserData', 1, [
            'device_manufacturer'   => 'huawei',
            'device_is_mobile'      => 1
        ]);

        // ---- GROUP 1
        $group1 = new SegmentGroup;
        $group1->segment_id = $segment->id;
        $group1->save();

        $c1 = new SegmentGroupCriteria;
        $c1->segment_group_id = $group1->id;
        $c1->criteria_id = Criteria::where('slug', 'device_manufacturer')->first()->id;
        $c1->relation_id = Relation::where('symbol', 'NOT LIKE')->first()->id;
        $c1->value = 'asdf';
        $c1->save();

        $query = $segment->buildQuery();
        $items = $query->get();
        $ids = $items->map(function ($x) { return $x->id;});

        $this->assertCount(1, $items);

        $this->assertContains($userDataPass1->id, $ids);

        $this->assertNotContains($userDataNotPass1->id, $ids);
        $this->assertNotContains($userDataNotPass2->id, $ids);
    }

    /** @test */
    public function it_applies_bool_normalizer_with_true()
    {
        $this->signIn();

        $segment = create('Segment', 1, [
            'name'  => 'Active segment 1'
        ]);

        // Megfelelnek a feltételnek
        $userDataPass1 = create('UserData', 1, [
            'device_is_mobile'      => 1,
        ]);
        $userDataPass2 = create('UserData', 1, [
            'device_is_mobile'      => 1
        ]);

        // Nem felelnek meg a feltételeknek
        $userDataNotPass1 = create('UserData', 1, [
            'device_is_mobile'      => 0
        ]);

        // ---- GROUP 1
        $group1 = new SegmentGroup;
        $group1->segment_id = $segment->id;
        $group1->save();

        $c1 = new SegmentGroupCriteria;
        $c1->segment_group_id = $group1->id;
        $c1->criteria_id = Criteria::where('slug', 'device_is_mobile')->first()->id;
        $c1->relation_id = Relation::where('symbol', '=')->first()->id;
        $c1->value = 'igen';
        $c1->save();

        $query = $segment->buildQuery();
        $items = $query->get();
        $ids = $items->map(function ($x) { return $x->id;});

        $this->assertCount(2, $items);

        $this->assertContains($userDataPass1->id, $ids);
        $this->assertContains($userDataPass2->id, $ids);

        $this->assertNotContains($userDataNotPass1->id, $ids);
    }

    /** @test */
    public function it_applies_bool_normalizer_with_false()
    {
        $this->signIn();

        $segment = create('Segment', 1, [
            'name'  => 'Active segment 1'
        ]);

        // Megfelelnek a feltételnek
        $userDataNotPass1 = create('UserData', 1, [
            'device_is_mobile'      => 1,
        ]);
        $userDataNotPass2 = create('UserData', 1, [
            'device_is_mobile'      => 1
        ]);

        // Nem felelnek meg a feltételeknek
        $userDataPass1 = create('UserData', 1, [
            'device_is_mobile'      => 0
        ]);

        // ---- GROUP 1
        $group1 = new SegmentGroup;
        $group1->segment_id = $segment->id;
        $group1->save();

        $c1 = new SegmentGroupCriteria;
        $c1->segment_group_id = $group1->id;
        $c1->criteria_id = Criteria::where('slug', 'device_is_mobile')->first()->id;
        $c1->relation_id = Relation::where('symbol', '=')->first()->id;
        $c1->value = 'nem';
        $c1->save();

        $query = $segment->buildQuery();
        $items = $query->get();
        $ids = $items->map(function ($x) { return $x->id;});

        $this->assertCount(1, $items);

        $this->assertContains($userDataPass1->id, $ids);

        $this->assertNotContains($userDataNotPass1->id, $ids);
        $this->assertNotContains($userDataNotPass2->id, $ids);
    }
}
