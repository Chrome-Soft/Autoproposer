<?php

namespace Tests\Feature;

use App\Criteria;
use App\Relation;
use App\Segment;
use App\SegmentGroup;
use App\SegmentGroupCriteria;
use App\UserData;
use Illuminate\Foundation\Testing\DatabaseMigrations;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Config;

class SegmentEqualityTest extends TestCase
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
    public function it_returns_true_if_segment_group_criterias_equal()
    {
        $criteria1 = new SegmentGroupCriteria;
        $criteria1->criteria_id = 1;
        $criteria1->relation_id = 1;
        $criteria1->value = 'value';
        $criteria1->bool_type = null;

        $criteria2 = new SegmentGroupCriteria;
        $criteria2->criteria_id = 1;
        $criteria2->relation_id = 1;
        $criteria2->value = 'value';
        $criteria2->bool_type = null;

        $this->assertTrue($criteria1->sameAs($criteria2));
    }

    /** @test */
    public function it_returns_false_if_segment_group_criterias_not_equal()
    {
        $criteria1 = new SegmentGroupCriteria;
        $criteria1->criteria_id = 1;
        $criteria1->relation_id = 1;
        $criteria1->value = 'value';
        $criteria1->bool_type = null;

        $criteria2 = new SegmentGroupCriteria;
        $criteria2->criteria_id = 2;
        $criteria2->relation_id = 1;
        $criteria2->value = 'value';
        $criteria2->bool_type = null;

        $this->assertFalse($criteria1->sameAs($criteria2));

        $criteria3 = new SegmentGroupCriteria;
        $criteria3->criteria_id = 1;
        $criteria3->relation_id = 2;
        $criteria3->value = 'value';
        $criteria3->bool_type = null;

        $this->assertFalse($criteria1->sameAs($criteria3));

        $criteria4 = new SegmentGroupCriteria;
        $criteria4->criteria_id = 1;
        $criteria4->relation_id = 1;
        $criteria4->value = 'value1';
        $criteria4->bool_type = null;

        $this->assertFalse($criteria1->sameAs($criteria4));

        $criteria5 = new SegmentGroupCriteria;
        $criteria5->criteria_id = 1;
        $criteria5->relation_id = 1;
        $criteria5->value = 'value';
        $criteria5->bool_type = 'or';

        $this->assertFalse($criteria1->sameAs($criteria5));
    }

    /** @test **/
    public function it_returns_true_if_segment_groups_equal()
    {
        $group = new SegmentGroup;
        $group->bool_type = 'or';

        $group1 = new SegmentGroup;
        $group1->bool_type = 'or';

        $this->assertTrue($group->sameAs($group1));

        $segment = create('Segment');
        $group = new SegmentGroup;
        $group->segment_id = $segment->id;
        $group->save();

        $criteria = new SegmentGroupCriteria;
        $criteria->segment_group_id = $group->id;
        $criteria->criteria_id = 1;
        $criteria->relation_id = 1;
        $criteria->value = 'value';
        $criteria->bool_type = null;
        $criteria->save();

        $segment1 = create('Segment');
        $group1 = new SegmentGroup;
        $group1->segment_id = $segment1->id;
        $group1->save();

        $criteria1 = new SegmentGroupCriteria;
        $criteria1->segment_group_id = $group1->id;
        $criteria1->criteria_id = 1;
        $criteria1->relation_id = 1;
        $criteria1->value = 'value';
        $criteria1->bool_type = null;
        $criteria1->save();

        $this->assertTrue($group->sameAs($group1));
        $this->assertTrue($segment->sameAs($segment1));
    }

    /** @test **/
    public function it_returns_false_if_segment_groups_differ_by_criterias()
    {
        $group = new SegmentGroup;
        $group->bool_type = 'or';

        $group1 = new SegmentGroup;
        $group1->bool_type = 'or';

        $this->assertTrue($group->sameAs($group1));

        $segment = create('Segment');
        $group = new SegmentGroup;
        $group->segment_id = $segment->id;
        $group->save();

        $criteria = new SegmentGroupCriteria;
        $criteria->segment_group_id = $group->id;
        $criteria->criteria_id = 1;
        $criteria->relation_id = 1;
        $criteria->value = 'value';
        $criteria->bool_type = null;
        $criteria->save();

        $segment1 = create('Segment');
        $group1 = new SegmentGroup;
        $group1->segment_id = $segment1->id;
        $group1->save();

        $criteria1 = new SegmentGroupCriteria;
        $criteria1->segment_group_id = $group1->id;
        $criteria1->criteria_id = 1;
        $criteria1->relation_id = 2;
        $criteria1->value = 'value';
        $criteria1->bool_type = null;
        $criteria1->save();

        $this->assertFalse($group->sameAs($group1));
        $this->assertFalse($segment->sameAs($segment1));
    }

    /** @test */
    public function it_returns_true_if_has_same_segment()
    {
        $segment = new Segment;
        $segment->name = 'segment 1';
        $segment->save();

        $group = new SegmentGroup;
        $group->bool_type = 'or';
        $group->segment_id = $segment->id;
        $group->save();

        $segment1 = new Segment;
        $segment1->name = 'segment 2';
        $segment1->save();

        $group1 = new SegmentGroup;
        $group1->bool_type = 'or';
        $group1->segment_id = $segment1->id;
        $group1->save();

        $this->assertTrue($segment->hasSame());
    }

    /** @test */
    public function it_returns_false_if_dont_have_same_segment()
    {
        $segment = new Segment;
        $segment->name = 'segment 1';
        $segment->save();

        $group = new SegmentGroup;
        $group->bool_type = 'or';
        $group->segment_id = $segment->id;
        $group->save();

        $segment1 = new Segment;
        $segment1->name = 'segment 2';
        $segment1->save();

        $group1 = new SegmentGroup;
        $group1->bool_type = 'and';
        $group1->segment_id = $segment1->id;
        $group1->save();

        $this->assertFalse($segment->hasSame());
    }

    /** @test */
    public function regression_it_returns_true_with_multiple_criterias()
    {
        $segment = create('Segment');
        $group = new SegmentGroup;
        $group->segment_id = $segment->id;
        $group->save();

        $criteria = new SegmentGroupCriteria;
        $criteria->segment_group_id = $group->id;
        $criteria->criteria_id = 1;
        $criteria->relation_id = 1;
        $criteria->value = 'value';
        $criteria->bool_type = 'or';
        $criteria->save();

        $criteria1 = new SegmentGroupCriteria;
        $criteria1->segment_group_id = $group->id;
        $criteria1->criteria_id = 1;
        $criteria1->relation_id = 2;
        $criteria1->value = 'value2';
        $criteria1->bool_type = null;
        $criteria1->save();

        $segment1 = create('Segment');
        $group1 = new SegmentGroup;
        $group1->segment_id = $segment1->id;
        $group1->save();

        $criteria2 = new SegmentGroupCriteria;
        $criteria2->segment_group_id = $group1->id;
        $criteria2->criteria_id = 1;
        $criteria2->relation_id = 1;
        $criteria2->value = 'value';
        $criteria2->bool_type = 'or';
        $criteria2->save();

        $criteria3 = new SegmentGroupCriteria;
        $criteria3->segment_group_id = $group1->id;
        $criteria3->criteria_id = 1;
        $criteria3->relation_id = 2;
        $criteria3->value = 'value2';
        $criteria3->bool_type = null;
        $criteria3->save();

        $this->assertTrue($segment->sameAs($segment1));
    }

    /** @test */
    public function regression_it_returns_true_with_multiple_criterias_independnetly_of_the_order()
    {
        $segment = create('Segment');
        $group = new SegmentGroup;
        $group->segment_id = $segment->id;
        $group->save();

        $criteria1 = new SegmentGroupCriteria;
        $criteria1->segment_group_id = $group->id;
        $criteria1->criteria_id = 1;
        $criteria1->relation_id = 2;
        $criteria1->value = 'value2';
        $criteria1->bool_type = null;
        $criteria1->save();

        $criteria = new SegmentGroupCriteria;
        $criteria->segment_group_id = $group->id;
        $criteria->criteria_id = 1;
        $criteria->relation_id = 1;
        $criteria->value = 'value';
        $criteria->bool_type = 'or';
        $criteria->save();

        $segment1 = create('Segment');
        $group1 = new SegmentGroup;
        $group1->segment_id = $segment1->id;
        $group1->save();

        $criteria2 = new SegmentGroupCriteria;
        $criteria2->segment_group_id = $group1->id;
        $criteria2->criteria_id = 1;
        $criteria2->relation_id = 1;
        $criteria2->value = 'value';
        $criteria2->bool_type = 'or';
        $criteria2->save();

        $criteria3 = new SegmentGroupCriteria;
        $criteria3->segment_group_id = $group1->id;
        $criteria3->criteria_id = 1;
        $criteria3->relation_id = 2;
        $criteria3->value = 'value2';
        $criteria3->bool_type = null;
        $criteria3->save();

        $this->assertTrue($segment->sameAs($segment1));
    }

    /** @test */
    public function regression_it_always_returns_false_if_default_segment()
    {
        /**
         * Egyéb szegmensnek nem lehet a csoportjait szerkeszteni, csak neve van lírása, és template
         * A name mező unique db és validáció szinten is, tehát default esetén nem kell semmit
         * vizsgálni, mindig false -t ad vissza
         */
        $segment = create('Segment', 1, ['is_default' => 1]);
        $group = new SegmentGroup;
        $group->segment_id = $segment->id;
        $group->save();

        $criteria1 = new SegmentGroupCriteria;
        $criteria1->segment_group_id = $group->id;
        $criteria1->criteria_id = 1;
        $criteria1->relation_id = 2;
        $criteria1->value = 'value2';
        $criteria1->bool_type = null;
        $criteria1->save();

        $criteria = new SegmentGroupCriteria;
        $criteria->segment_group_id = $group->id;
        $criteria->criteria_id = 1;
        $criteria->relation_id = 1;
        $criteria->value = 'value';
        $criteria->bool_type = 'or';
        $criteria->save();

        $segment1 = create('Segment');
        $group1 = new SegmentGroup;
        $group1->segment_id = $segment1->id;
        $group1->save();

        $criteria2 = new SegmentGroupCriteria;
        $criteria2->segment_group_id = $group1->id;
        $criteria2->criteria_id = 1;
        $criteria2->relation_id = 1;
        $criteria2->value = 'value';
        $criteria2->bool_type = 'or';
        $criteria2->save();

        $criteria3 = new SegmentGroupCriteria;
        $criteria3->segment_group_id = $group1->id;
        $criteria3->criteria_id = 1;
        $criteria3->relation_id = 2;
        $criteria3->value = 'value2';
        $criteria3->bool_type = null;
        $criteria3->save();

        $this->assertFalse($segment->sameAs($segment1));
    }
}
