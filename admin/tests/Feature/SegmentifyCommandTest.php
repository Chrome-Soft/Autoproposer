<?php

namespace Tests\Feature;

use App\Console\Commands\Segmentify;
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
use App\Services\HttpClient;
use App\Services\Recommender\RecommenderService;
use App\UserData;
use Illuminate\Foundation\Testing\DatabaseMigrations;
use Illuminate\Foundation\Testing\WithoutMiddleware;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Foundation\Testing\RefreshDatabase;

class SegmentifyCommandTest extends TestCase
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
    public function it_fills_out_empty_default_and_own_user_data()
    {
        $segment = $this->createSegment();
        $defaultSegment = Segment::where('is_default', 1)->first();

        $userDataPass1 = create('UserData', 1, [
            'device_manufacturer'   => 'apple',
            'segment_id'            => $segment->id
        ]);

        $userDataPass2 = create('UserData', 1, [
            'device_manufacturer'   => 'apple',
            'segment_id'            => null
        ]);

        $userDataPass3 = create('UserData', 1, [
            'device_manufacturer'   => 'apple',
            'segment_id'            => $defaultSegment->id
        ]);

        $serviceMock = $this->createMock(RecommenderService::class);
        $serviceMock->method('segmentify')
            ->willReturn($segment->id);

        $this->app->instance(RecommenderService::class, $serviceMock);

        $this->artisan('segment:segmentify', [
            'segment'   => $segment->slug
        ]);

        $otherUserDatas = UserData::where('segment_id', '!=', $segment->id)->get();
        $this->assertEmpty($otherUserDatas);
    }

    /** @test */
    public function it_should_not_throw_error_when_no_user_data()
    {
        $segment = create('Segment', 1, [
            'name'  => 'Segment 1'
        ]);

        $serviceMock = $this->createMock(RecommenderService::class);
        $serviceMock->method('segmentify')
            ->willReturn(1);

        $this->app->instance(RecommenderService::class, $serviceMock);

        $this->artisan('segment:segmentify', [
            'segment'   => $segment->slug
        ]);

        // Kicsit furán néz ki, de phpunit -ban csak expectedException assert létezik
        $this->assertTrue(true);
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
}
