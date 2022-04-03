<?php

namespace Tests\Feature;

use App\Criteria;
use App\Currency;
use App\Page;
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

class PageDeleteTest extends TestCase
{
    use DatabaseMigrations;

    protected function setUp(): void
    {
        parent::setUp();
        $this->withExceptionHandling();

        $this->artisan('db:seed', ['--class' => 'CriteriaTableSeeder']);
    }

    /** @test */
    public function removes_group_criteria_if_it_uses_page_as_condition()
    {
        Cache::shouldReceive('get')
            ->times(1)
            ->with('criterias')
            ->andReturn(Criteria::all());

        $this->signIn();

        $partner = create('Partner');
        $page1 = new Page;
        $page1->name = 'Page';
        $page1->url = '/page';
        $page1->partner_id = $partner->id;
        $page1->save();

        $page2 = new Page;
        $page2->name = 'Page2';
        $page2->url = '/page2';
        $page2->partner_id = $partner->id;
        $page2->save();

        $segment = new Segment;
        $segment->name = 'segmant';
        $segment->user_id = $this->signedInUser->id;
        $segment->save();

        $group = new SegmentGroup;
        $group->segment_id = $segment->id;
        $group->save();

        $criteriaPage1 = new SegmentGroupCriteria;
        $criteriaPage1->segment_group_id = $group->id;
        $criteriaPage1->criteria_id = Criteria::where('slug', 'visited_path')->first()->id;
        $criteriaPage1->relation_id = 1;
        $criteriaPage1->value = json_encode(['page_id' => $page1->id]);
        $criteriaPage1->save();

        $criteriaPage2 = new SegmentGroupCriteria;
        $criteriaPage2->segment_group_id = $group->id;
        $criteriaPage2->criteria_id = Criteria::where('slug', 'visited_path')->first()->id;
        $criteriaPage2->relation_id = 1;
        $criteriaPage2->value = json_encode(['page_id' => $page2->id]);
        $criteriaPage2->save();

        $otherCriteria = new SegmentGroupCriteria;
        $otherCriteria->segment_group_id = $group->id;
        $otherCriteria->criteria_id = Criteria::where('slug', 'device_manufacturer')->first()->id;
        $otherCriteria->relation_id = 1;
        $otherCriteria->value = 'asdf';
        $otherCriteria->save();

        $page1->delete();

        $x1 = Page::where('id', $page1->id)->first();
        $x2 = Page::where('id', $page2->id)->first();

        $this->assertNull($x1);
        $this->assertNotNull($x2);

        $this->assertDatabaseMissing('segment_group_criterias', [
            'segment_group_id'  => $group->id,
            'value'             => json_encode(['page_id' => $page1->id])
        ]);
        $this->assertDatabaseHas('segment_group_criterias', [
            'segment_group_id'  => $group->id,
            'value'             => json_encode(['page_id' => $page2->id])
        ]);

        $this->assertDatabaseHas('segment_group_criterias', [
            'segment_group_id'  => $group->id,
            'criteria_id'       => Criteria::where('slug', 'device_manufacturer')->first()->id
        ]);
    }
}
