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
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Foundation\Testing\RefreshDatabase;

class SegmentifyTest extends TestCase
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
    public function it_segmentifies_user_data()
    {
        $segment = $this->createSegment();

        $userData1 = create('UserData', 1, [
            'device_manufacturer'   => 'apple'
        ]);
        $userData2 = create('UserData', 1, [
            'device_manufacturer'   => 'apple'
        ]);

        $segment->segmentify([$userData1->id, $userData2->id]);

        $userData1 = $userData1->refresh();
        $this->assertEquals($segment->id, $userData1->segment_id);

        $userData2 = $userData2->refresh();
        $this->assertEquals($segment->id, $userData2->segment_id);
    }

    /** @test */
    public function it_unsegmentifies_user_data()
    {
        $segment = $this->createSegment();
        $otherSegment = create('Segment', 1, [
            'name'  => 'Segment 2'
        ]);

        $userDataPass1 = create('UserData', 1, [
            'device_manufacturer'   => 'apple',
            'segment_id'            => $segment->id
        ]);
        $userDataPass2 = create('UserData', 1, [
            'device_manufacturer'   => 'apple',
            'segment_id'            => $segment->id
        ]);

        $userDataNotPass2 = create('UserData', 1, [
            'device_manufacturer'   => 'apple',
            'segment_id'            => $otherSegment->id
        ]);

        $segment->unsegmentify();

        $userDataPass1 = $userDataPass1->refresh();
        $this->assertNull($userDataPass1->segment_id);

        $userDataPass2 = $userDataPass2->refresh();
        $this->assertNull($userDataPass2->segment_id);

        $this->assertNotNull($userDataNotPass2->segment_id);
    }

    protected function createSegment()
    {
        $segment = create('Segment', 1, [
            'name'  => 'Segment 1'
        ]);

        $group = new SegmentGroup;
        $group->segment_id = $segment->id;
        $group->save();

        $c = new SegmentGroupCriteria;
        $c->segment_group_id = $group->id;
        $c->criteria_id = Criteria::where('slug', 'device_manufacturer')->first()->id;
        $c->relation_id = Relation::where('symbol', '=')->first()->id;
        $c->value = 'apple';
        $c->save();

        return $segment;
    }

    /** @test */
    public function it_returns_the_most_specific_segment_if_more_then_one_possible_to_user_data()
    {
        $segmentOneCriteria = create('Segment', 1, [
            'name'  => 'Segment 1'
        ]);

        $group = new SegmentGroup;
        $group->segment_id = $segmentOneCriteria->id;
        $group->save();

        $c = new SegmentGroupCriteria;
        $c->segment_group_id = $group->id;
        $c->criteria_id = Criteria::where('slug', 'device_manufacturer')->first()->id;
        $c->relation_id = Relation::where('symbol', '=')->first()->id;
        $c->value = 'apple';
        $c->save();

        // -------

        $segmentTwoCriteria = create('Segment', 1, [
            'name'  => 'Segment 2'
        ]);

        $group = new SegmentGroup;
        $group->segment_id = $segmentTwoCriteria->id;
        $group->save();

        $c = new SegmentGroupCriteria;
        $c->segment_group_id = $group->id;
        $c->criteria_id = Criteria::where('slug', 'device_manufacturer')->first()->id;
        $c->relation_id = Relation::where('symbol', '=')->first()->id;
        $c->value = 'apple';
        $c->save();

        $c = new SegmentGroupCriteria;
        $c->segment_group_id = $group->id;
        $c->criteria_id = Criteria::where('slug', 'device_product')->first()->id;
        $c->relation_id = Relation::where('symbol', '=')->first()->id;
        $c->value = 'iphone';
        $c->save();

        // ------

        $userData = create('UserData', 1, [
            'device_manufacturer'   => 'apple',
            'device_product'        => 'iphone'
        ]);

        $predictedSegmentId = $userData->segmentify();

        $this->assertEquals($segmentTwoCriteria->id, $predictedSegmentId);
    }

    /** @test */
    public function regression_it_returns_default_if_no_segments()
    {
        $userData = create('UserData', 1, [
            'device_manufacturer'   => 'apple',
            'device_product'        => 'iphone'
        ]);

        $predictedSegmentId = $userData->segmentify();

        $defaultSegment = Segment::where('is_default', 1)->first();
        $this->assertEquals($defaultSegment->id, $predictedSegmentId);
    }
}
