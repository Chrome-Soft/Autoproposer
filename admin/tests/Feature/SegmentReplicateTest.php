<?php

namespace Tests\Feature;

use App\Currency;
use App\PageLoad;
use App\Product;
use App\ProductAttributeType;
use App\ProductProductAttribute;
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

class SegmentReplicateTest extends TestCase
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
    public function replicate_segment()
    {
        $user = create('User');
        $this->actingAs($user, 'api');

        $segment = create('Segment', 1, ['name' => 'Segment 1']);
        $group1 = new SegmentGroup;
        $group1->segment_id = $segment->id;
        $group1->bool_type = '';
        $group1->save();

        $criteria1 = new SegmentGroupCriteria;
        $criteria1->segment_group_id = $group1->id;
        $criteria1->criteria_id = 1;
        $criteria1->relation_id = 1;
        $criteria1->value = 'value 1';
        $criteria1->bool_type = 'and';
        $criteria1->save();

        $criteria2 = new SegmentGroupCriteria;
        $criteria2->segment_group_id = $group1->id;
        $criteria2->criteria_id = 2;
        $criteria2->relation_id = 1;
        $criteria2->value = 'value 2';
        $criteria2->save();

        $response = $this->post("/api/segments/{$segment->slug}/replicate", $segment->toArray());
        $response->assertStatus(200);

        $segmentResponse = json_decode($response->getContent(), true);

        $this->assertDatabaseHas('segments', [
            'name'  => 'Segment 1 Másolat'
        ]);

        $segmentReplicate = Segment::where('id', $segmentResponse['id'])->first();
        $this->assertCount(1, $segmentReplicate->groups);
        $this->assertCount(2, $segmentReplicate->groups->first()->criterias);
    }
}
