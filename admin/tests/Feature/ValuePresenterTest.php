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

class ValuePresenterTest extends TestCase
{
    use DatabaseMigrations;

    protected function setUp(): void
    {
        parent::setUp();
        $this->withExceptionHandling();

        $this->artisan('db:seed', ['--class' => 'CriteriaTableSeeder']);
    }

    /** @test */
    public function it_presents_visited_url()
    {
        $page = create('Page', 1, ['name' => 'Kapcsolat']);
        $segment = create('Segment');
        $group = create('SegmentGroup', 1, ['segment_id' => $segment->id]);

        $criteria = new SegmentGroupCriteria;
        $criteria->segment_group_id = $group->id;
        $criteria->criteria_id = (Criteria::where('slug', 'visited_url')->first())->id;
        $criteria->relation_id = Relation::EQUAL;
        $criteria->value = json_encode(['page_id' => $page->id]);

        $criteria->save();

        $this->assertEquals('Kapcsolat', $criteria->normalizedValue);
    }

    /** @test */
    public function it_presents_visited_path()
    {
        $fromPage = create('Page', 1, ['name' => 'Kapcsolat']);
        $toPage = create('Page', 1, ['name' => 'Info']);

        $segment = create('Segment');
        $group = create('SegmentGroup', 1, ['segment_id' => $segment->id]);

        $criteria = new SegmentGroupCriteria;
        $criteria->segment_group_id = $group->id;
        $criteria->criteria_id = (Criteria::where('slug', 'visited_path')->first())->id;
        $criteria->relation_id = Relation::EQUAL;
        $criteria->value = json_encode(['from_page_id' => $fromPage->id, 'to_page_id' => $toPage->id]);

        $criteria->save();

        $this->assertEquals('Kapcsolat -> Info', $criteria->normalizedValue);
    }

    /** @test */
    public function it_presents_identity_value()
    {
        $segment = create('Segment');
        $group = create('SegmentGroup', 1, ['segment_id' => $segment->id]);

        $criteria = new SegmentGroupCriteria;
        $criteria->segment_group_id = $group->id;
        $criteria->criteria_id = (Criteria::where('slug', 'os_name')->first())->id;
        $criteria->relation_id = Relation::EQUAL;
        $criteria->value = 'iOS';

        $criteria->save();

        $this->assertEquals('iOS', $criteria->normalizedValue);
    }
}
